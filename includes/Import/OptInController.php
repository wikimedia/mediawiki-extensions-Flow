<?php

namespace Flow\Import;

use DateTime;
use DateTimeZone;
use DerivativeContext;
use Flow\Collection\HeaderCollection;
use Flow\Content\BoardContent;
use Flow\Exception\InvalidDataException;
use Flow\NotificationController;
use Flow\OccupationController;
use Flow\Parsoid\Utils;
use Flow\RevisionActionPermissions;
use Flow\WorkflowLoader;
use Flow\WorkflowLoaderFactory;
use IContextSource;
use MovePage;
use RequestContext;
use Revision;
use Title;
use Flow\Container;
use User;
use WikiPage;
use WikitextContent;

/**
 * Entry point for enabling Flow on a page.
 */
class OptInController {

	/**
	 * @var OccupationController
	 */
	private $occupationController;

	/**
	 * @var NotificationController
	 */
	private $notificationController;

	/**
	 * @var ArchiveNameHelper
	 */
	private $archiveNameHelper;

	/**
	 * @var IContextSource
	 */
	private $context;

	/**
	 * @var User
	 */
	private $user;

	public function __construct() {
		$this->occupationController = Container::get( 'occupation_controller' );
		$this->notificationController = Container::get( 'controller.notification' );
		$this->archiveNameHelper = new ArchiveNameHelper();
		$this->user = $this->occupationController->getTalkpageManager();
		$this->context = new DerivativeContext( RequestContext::getMain() );
		$this->context->setUser( $this->user );
	}

	/**
	 * @param Title $title
	 * @param User $user
	 */
	public function enable( Title $title, User $user ) {
		if ( $this->isFlowBoard( $title ) ) {
			// already a Flow board
			return;
		}

		// archive existing wikitext talk page
		$linkToArchivedTalkpage = null;
		if ( $title->exists( Title::GAID_FOR_UPDATE ) ) {
			$wikitextTalkpageArchiveTitle = $this->archiveExistingTalkpage( $title );
			$this->addArchiveTemplate( $wikitextTalkpageArchiveTitle, $title );
			$linkToArchivedTalkpage = $this->buildLinkToArchivedTalkpage( $wikitextTalkpageArchiveTitle );
		}

		// create or restore flow board
		$archivedFlowPage = $this->findLatestFlowArchive( $title );
		if ( $archivedFlowPage ) {
			$this->restoreExistingFlowBoard( $archivedFlowPage, $title, $linkToArchivedTalkpage );
		} else {
			$this->createFlowBoard( $title, $linkToArchivedTalkpage );
			$this->notificationController->notifyFlowEnabledOnTalkpage( $user );
		}
	}

	/**
	 * @param Title $title
	 */
	public function disable( Title $title ) {
		if ( !$this->isFlowBoard( $title ) ) {
			return;
		}

		// archive the flow board
		$flowArchiveTitle = $this->findNextFlowArchive( $title );
		$this->movePage( $title, $flowArchiveTitle );
		$this->removeArchivedTalkpageTemplateFromFlowBoardDescription( $flowArchiveTitle );

		// restore the original wikitext talk page
		$archivedTalkpage = $this->findLatestArchive( $title );
		if ( $archivedTalkpage ) {
			$this->movePage( $archivedTalkpage, $title );
			$this->removeArchiveTemplateFromWikitextTalkpage( $title );
		}
	}

	/**
	 * Check whether the current user has a flow board archived already.
	 *
	 * @return boolean Flow board archive exists
	 */
	public function hasFlowBoardArchive( User $user ) {
		return $this->findLatestFlowArchive( $user->getTalkPage() ) !== false;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isFlowBoard( Title $title ) {
		return $title->getContentModel( Title::GAID_FOR_UPDATE ) === CONTENT_MODEL_FLOW_BOARD;
	}

	/**
	 * @param Title $from
	 * @param Title $to
	 */
	private function movePage( Title $from, Title $to ) {
		$mp = new MovePage( $from, $to );
		$mp->move( $this->user, null, false );

		/*
		 * Article IDs are cached inside title objects. Since we'll be
		 * reusing these objects, we have to make sure they reflect the
		 * correct IDs.
		 * We could just Title::GAID_FOR_UPDATE everywhere, but that would
		 * result in a lot of unneeded calls to master.
		 * If these IDs are wrong, we could end up associating workflows
		 * with an incorrect page (that was just moved)
		 *
		 * Anyway, the page has just been moved without redirect, so that
		 * page is no longer valid.
		 */
		$from->resetArticleID( 0 );
		$linkCache = \LinkCache::singleton();
		$linkCache->addBadLinkObj( $from );

		/*
		 * Force id cached inside $title to be updated, as well as info
		 * inside LinkCache.
		 */
		$to->getArticleID( Title::GAID_FOR_UPDATE );
	}

	/**
	 * @param $msgKey
	 * @param array $args
	 * @throws ImportException
	 */
	private function fatal( $msgKey, $args = array() ) {
		throw new ImportException( wfMessage( $msgKey, $args )->inContentLanguage()->text() );
	}

	/**
	 * @param string $str
	 * @return array
	 */
	private function fromNewlineSeparated( $str ) {
		return explode( "\n", $str );
	}

	/**
	 * @param Title $title
	 * @return Title|false
	 */
	private function findLatestArchive( Title $title ) {
		$archiveFormats = $this->fromNewlineSeparated(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return $this->archiveNameHelper->findLatestArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title
	 * @throws ImportException
	 */
	private function findNextArchive( Title $title ) {
		$archiveFormats = $this->fromNewlineSeparated(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return $this->archiveNameHelper->decideArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title|false
	 */
	private function findLatestFlowArchive( Title $title ) {
		$archiveFormats = $this->fromNewlineSeparated(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		return $this->archiveNameHelper->findLatestArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title
	 * @throws ImportException
	 */
	private function findNextFlowArchive( Title $title ) {
		$archiveFormats = $this->fromNewlineSeparated(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		return $this->archiveNameHelper->decideArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @param string $contentText
	 * @param string $summary
	 * @throws ImportException
	 * @throws \MWException
	 */
	private function createRevision( Title $title, $contentText, $summary ) {
		$page = WikiPage::factory( $title );
		$newContent = new WikitextContent( $contentText );
		$status = $page->doEditContent(
			$newContent,
			$summary,
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->user
		);

		if ( !$status->isGood() ) {
			throw new ImportException( "Failed creating revision at {$title}" );
		}
	}

	/**
	 * @param Title $title
	 * @param $boardDescription
	 * @throws ImportException
	 * @throws \Flow\Exception\CrossWikiException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	private function createFlowBoard( Title $title, $boardDescription ) {
		/** @var WorkflowLoaderFactory $loaderFactory */
		$loaderFactory = Container::get( 'factory.loader.workflow' );
		$page = $title->getPrefixedText();

		$allowCreationStatus = $this->occupationController->allowCreation( $title, $this->user, false );
		if ( !$allowCreationStatus->isGood() ) {
			$this->fatal( 'flow-special-enableflow-board-creation-not-allowed', $page );
		}

		$loader = $loaderFactory->createWorkflowLoader( $title );
		$blocks = $loader->getBlocks();

		if ( !$boardDescription ) {
			$boardDescription = ' ';
		}

		$action = 'edit-header';
		$params = array(
			'header' => array(
				'content' => $boardDescription,
				'format' => 'wikitext',
			),
		);

		$blocksToCommit = $loader->handleSubmit(
			$this->context,
			$action,
			$params
		);

		foreach ( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach ( $errors as $errorKey ) {
					$this->fatal( $block->getErrorMessage( $errorKey ) );
				}
			}
		}

		$loader->commit( $blocksToCommit );
	}

	/**
	 * @param Title $title
	 * @return Title
	 */
	private function archiveExistingTalkpage( Title $title ) {
		$archiveTitle = $this->findNextArchive( $title );
		$this->movePage( $title, $archiveTitle );
		return $archiveTitle;
	}

	/**
	 * @param Title $archivedFlowPage
	 * @param Title $title
	 * @param string|null $addToHeader
	 */
	private function restoreExistingFlowBoard( Title $archivedFlowPage, Title $title, $addToHeader = null ) {
		$this->movePage( $archivedFlowPage, $title );

		if ( $addToHeader ) {
			$this->editBoardDescription( $title, function( $oldDesc ) use ( $addToHeader ) {
				return $oldDesc . "\n\n" . $addToHeader;
			}, 'wikitext' );
		}
	}

	/**
	 * @param Title $title
	 * @return string
	 * @throws \MWException
	 */
	private function getContent( Title $title ) {
		$page = WikiPage::factory( $title );
		$page->loadPageData( 'fromdbmaster' );
		$revision = $page->getRevision();
		if ( $revision ) {
			$content = $revision->getContent( Revision::FOR_PUBLIC );
			if ( $content instanceof WikitextContent ) {
				return $content->getNativeData();
			}
		}

		return '';
	}

	/**
	 * @param Title $archiveTitle
	 * @return string
	 */
	private function buildLinkToArchivedTalkpage( Title $archiveTitle ) {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = array(
			'archive' => $archiveTitle->getPrefixedText(),
			'date' => $now->format( 'Y-m-d' ),
		);
		$template = wfMessage( 'flow-importer-wt-converted-template' )->inContentLanguage()->plain();
		return $this->formatTemplate( $template, $arguments );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return string
	 */
	private function formatTemplate( $name, $args ) {
		$arguments = implode( '|',
			array_map(
				function( $key, $value ) {
					return "$key=$value";
				},
				array_keys( $args ),
				array_values( $args ) )
		);
		return "{{{$name}|$arguments}}";
	}

	/**
	 * @param Title $flowArchiveTitle
	 */
	private function removeArchivedTalkpageTemplateFromFlowBoardDescription( Title $flowArchiveTitle ) {
		$this->editBoardDescription( $flowArchiveTitle, function( $oldDesc ) {
			$templateName = wfMessage( 'flow-importer-wt-converted-template' )->inContentLanguage()->plain();
			return TemplateHelper::removeFromHtml( $oldDesc, $templateName );
		}, 'html' );
	}

	/**
	 * @param Title $title
	 * @param callable $newDescriptionCallback
	 * @param string $format
	 * @throws ImportException
	 * @throws \Flow\Exception\InvalidDataException
	 */
	private function editBoardDescription( Title $title, callable $newDescriptionCallback, $format = 'html' ) {
		/*
		 * We could use WorkflowLoaderFactory::createWorkflowLoader
		 * to get to the workflow ID, but that uses WikiPage::factory
		 * to build the wikipage & get the content. For most requests,
		 * that'll be better (it reads from slaves), but we really
		 * need to read from master here.
		 * We'll need WorkflowLoader further down anyway, but we'll
		 * then have the correct workflow ID to initialize it with!
		 *
		 * $title->getLatestRevId() should be fine, it'll be read from
		 * LinkCache, which has been updated.
		 * Revision::newFromId will try slave first. If it can't find
		 * the id, it'll try to find it on master.
		 */
		$revId = $title->getLatestRevID();
		$revision = Revision::newFromId( $revId );
		$content = $revision->getContent();
		if ( !$content instanceof BoardContent ) {
			throw new InvalidDataException(
				'Could not find board page for ' . $title->getPrefixedDBkey() . ' (id: ' . $title->getArticleID() . ').' .
				'Found content: ' . var_export( $content, true )
			);
		}
		$workflowId = $content->getWorkflowId();

		$collection = HeaderCollection::newFromId( $workflowId );
		$revision = $collection->getLastRevision();

		/*
		 * We could just do $revision->getContent( $format ), but that
		 * may need to find $title in order to convert.
		 * We already know $title (and don't want to risk it being used
		 * in a way it stores lagging slave data), so let's just
		 * manually convert the content.
		 */
		$content = $revision->getContentRaw();
		$content = Utils::convert( $revision->getContentFormat(), $format, $content, $title );

		$newDescription = call_user_func( $newDescriptionCallback, $content );

		$action = 'edit-header';
		$params = array(
			'header' => array(
				'content' => $newDescription,
				'format' => $format,
				'prev_revision' => $revision->getRevisionId()->getAlphadecimal()
			),
		);

		/** @var WorkflowLoaderFactory $factory */
		$factory = Container::get( 'factory.loader.workflow' );

		/** @var WorkflowLoader $loader */
		$loader = $factory->createWorkflowLoader( $title, $workflowId );

		$blocks = $loader->getBlocks();

		$blocksToCommit = $loader->handleSubmit(
			$this->context,
			$action,
			$params
		);

		foreach ( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach ( $errors as $errorKey ) {
					$this->fatal( $block->getErrorMessage( $errorKey ) );
				}
			}
		}

		$loader->commit( $blocksToCommit );
	}

	/**
	 * @param Title $archive
	 * @param Title $current
	 * @throws ImportException
	 */
	private function addArchiveTemplate( Title $archive, Title $current ) {
		$templateName = wfMessage( 'flow-importer-wt-converted-archive-template' )->inContentLanguage()->plain();
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$template = $this->formatTemplate( $templateName, array(
			'from' => $current->getPrefixedText(),
			'date' => $now->format( 'Y-m-d' ),
		) );

		$content = $this->getContent( $archive );

		$this->createRevision(
			$archive,
			$template . "\n\n" . $content,
			wfMessage( 'flow-beta-feature-add-archive-template-edit-summary' )->inContentLanguage()->plain());
	}

	/**
	 * @param Title $title
	 * @throws ImportException
	 */
	private function removeArchiveTemplateFromWikitextTalkpage( Title $title ) {
		$content = $this->getContent( $title );
		if ( !$content ) {
			return;
		}

		$content = Utils::convert( 'wikitext', 'html', $content, $title );
		$templateName = wfMessage( 'flow-importer-wt-converted-archive-template' )->inContentLanguage()->plain();

		$newContent = TemplateHelper::removeFromHtml( $content, $templateName );

		$this->createRevision(
			$title,
			Utils::convert( 'html', 'wikitext', $newContent, $title ),
			wfMessage( 'flow-beta-feature-remove-archive-template-edit-summary' )->inContentLanguage()->plain());
	}

}
