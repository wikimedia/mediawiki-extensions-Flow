<?php

namespace Flow\Import;

use DateTime;
use DateTimeZone;
use Flow\NotificationController;
use Flow\OccupationController;
use Flow\WorkflowLoaderFactory;
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

	public function __construct() {
		$this->occupationController = Container::get( 'occupation_controller' );
		$this->notificationController = Container::get( 'controller.notification' );
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

		$boardDescription = wfMessage( 'flow-talk-page-beta-feature-welcome-message' )->inContentLanguage()->text();

		// archive existing wikitext talk page
		if ( $title->exists( Title::GAID_FOR_UPDATE ) ) {
			$archiveTitle = $this->archiveExistingTalkpage( $title );
			$boardDescription .= "\n\n" . $this->buildBoardDescription( $archiveTitle );
		}

		// create or restore flow board
		$archivedFlowPage = $this->findLatestFlowArchive( $title );
		if ( $archivedFlowPage ) {
			$this->restoreExistingFlowBoard( $archivedFlowPage, $title );
		} else {
			$this->createFlowBoard( $title, $boardDescription );
		}

		$this->notificationController->notifyFlowEnabledOnTalkpage( $user );
	}

	/**
	 * @param Title $title
	 */
	public function disable( Title $title ) {
		if ( !$this->isFlowBoard( $title ) ) {
			return;
		}

		// archive the flow page
		$flowArchiveTitle = $this->findNextFlowArchive( $title );
		$this->movePage( $title, $flowArchiveTitle );

		// restore the original wikitext talk page
		$archivedTalkpage = $this->findLatestArchive( $title );
		if ( $archivedTalkpage ) {
			// restore the previously archived wikitext talk page
			$this->movePage( $archivedTalkpage, $title );
		}

		// ensure the archived flow page is referenced on the wikitext talk page
		$this->ensureHasLinkToArchivedFlowBoard( $title, $flowArchiveTitle );
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isFlowBoard( Title $title ) {
		return $this->occupationController->isTalkpageOccupied( $title, true );
	}

	/**
	 * @param Title $from
	 * @param Title $to
	 */
	private function movePage( Title $from, Title $to ) {
		$flowTalkPageManagerUser = $this->occupationController->getTalkpageManager();

		$mp = new MovePage( $from, $to );
		$mp->move( $flowTalkPageManagerUser, null, false );

		// this was there in the previous version, don't know if it's needed
//		wfWaitForSlaves();
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
	private function from_csv( $str ) {
		return strpos( $str, "\n") === false ? array( $str ) : explode( "\n", $str );
	}

	/**
	 * @param Title $title
	 * @return Title|false
	 */
	private function findLatestArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return Converter::findLatestArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title
	 * @throws ImportException
	 */
	private function findNextArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return Converter::decideArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title|false
	 */
	private function findLatestFlowArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		return Converter::findLatestArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @return Title
	 * @throws ImportException
	 */
	private function findNextFlowArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		return Converter::decideArchiveTitle( $title, $archiveFormats );
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param string $contentText
	 * @throws ImportException
	 * @throws \MWException
	 */
	private function createRevision( Title $title, User $user, $contentText ) {
		$page = WikiPage::factory( $title );
		$newContent = new WikitextContent( $contentText );
		$status = $page->doEditContent(
			$newContent,
			null,
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$user
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

		$asUser = $this->occupationController->getTalkpageManager();
		$allowCreationStatus = $this->occupationController->allowCreation( $title, $asUser, false );
		if ( !$allowCreationStatus->isGood() ) {
			$this->fatal( 'flow-special-enableflow-board-creation-not-allowed', $page );
		}

		$loader = $loaderFactory->createWorkflowLoader( $title );
		$blocks = $loader->getBlocks();

		$action = 'edit-header';
		$params = array(
			'header' => array(
				'content' => $boardDescription,
				'format' => 'wikitext',
			),
		);

		$blocksToCommit = $loader->handleSubmit(
			RequestContext::getMain(),
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
	 */
	private function restoreExistingFlowBoard( Title $archivedFlowPage, Title $title ) {
		$this->movePage( $archivedFlowPage, $title );
	}

	/**
	 * @param Title $title
	 * @param Title $flowArchiveTitle
	 * @throws ImportException
	 */
	private function ensureHasLinkToArchivedFlowBoard( Title $title, Title $flowArchiveTitle ) {
		$content = $this->getContent( $title );
		$linkToArchiveTemplate = $this->formatTemplate(
			wfMessage( 'flow-importer-flow-converted-archive-template' )->inContentLanguage()->plain(),
			array( 'from' => $flowArchiveTitle->getPrefixedText() )
		);
		if ( strpos( $content, $linkToArchiveTemplate) === false ) {
			$content = $linkToArchiveTemplate . "\n\n" . $content;
		}
		$this->createRevision(
			$title,
			$this->occupationController->getTalkpageManager(),
			$content);
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
	private function buildBoardDescription( Title $archiveTitle ) {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = array(
			'from' => $archiveTitle->getPrefixedText(),
			'date' => $now->format( 'Y-m-d' ),
		);
		$template = wfMessage( 'flow-importer-wt-converted-archive-template' )->inContentLanguage()->plain();
		return $this->formatTemplate( $template, $arguments );
	}

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
}