<?php

namespace Flow\Import;

use DateTime;
use DateTimeZone;
use DerivativeContext;
use Flow\Collection\HeaderCollection;
use Flow\NotificationController;
use Flow\OccupationController;
use Flow\Parsoid\Utils;
use Flow\RevisionActionPermissions;
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

		// We need to replace the 'permissions' object in the container
		// so it is initialized with the user we are trying to
		// impersonate (Talk page manager user).
		$user = $this->user;
		Container::getContainer()->extend( 'permissions', function ( $p, $c ) use ( $user ) {
			return new RevisionActionPermissions( $c['flow_actions'], $user );
		} );
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
	public function hasFlowBoardArchive() {
		return $this->findLatestFlowArchive( $this->user->getTalkPage() ) !== false;
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

		// $title was recently moved, but the article ID is cached inside
		// the Title object. Let's make sure it accurately reflects that
		// $title now doesn't exist by forcefully re-fetching the non-
		// existing article ID.
		// Otherwise, we run the risk of the Workflow we're creating being
		// associated with the page we just moved.
		$title->getArticleID( Title::GAID_FOR_UPDATE );

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
		/** @var WorkflowLoaderFactory $loader */
		$factory = Container::get( 'factory.loader.workflow' );

		/** @var WorkflowLoader $loader */
		$loader = $factory->createWorkflowLoader( $title );

		$collection = HeaderCollection::newFromId( $loader->getWorkflow()->getId() );
		$revision = $collection->getLastRevision();
		$content = $revision->getContent();

		if ( $format === 'wikitext' ) {
			$content = Utils::convert( 'html', 'wikitext', $content, $title );
		}
		$newDescription = call_user_func( $newDescriptionCallback, $content );

		$action = 'edit-header';
		$params = array(
			'header' => array(
				'content' => $newDescription,
				'format' => $format,
				'prev_revision' => $revision->getRevisionId()->getAlphadecimal()
			),
		);

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
