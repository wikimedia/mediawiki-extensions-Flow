<?php

namespace Flow\Import;

use Flow\OccupationController;
use Flow\WorkflowLoaderFactory;
use MovePage;
use RequestContext;
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

	public function __construct() {
		$this->occupationController = Container::get( 'occupation_controller' );
	}

	/**
	 * @param Title $title
	 * @throws ImportException
	 */
	public function enable( Title $title ) {
		$boardDescription = 'Welcome to Flow';

		if ( $this->isAFlowBoard( $title ) ) {
			// already a Flow board
			return;
		}

		if ( $title->exists( Title::GAID_FOR_UPDATE ) ) {
			$this->archiveExistingTalkpage( $title );
		}

		$archivedFlowPage = $this->findLatestFlowArchive( $title );
		if ( $archivedFlowPage ) {
			$this->restoreExistingFlowBoard( $archivedFlowPage, $title );
		} else {
			$this->createFlowBoard( $title, $boardDescription );
		}
	}

	/**
	 * @param Title $title
	 */
	public function disable( Title $title ) {
		$flowTalkPageManagerUser = $this->occupationController->getTalkpageManager();

		// move the Flow page to an archive
		$flowArchiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		$flowArchiveTitle = Converter::decideArchiveTitle( $title, $flowArchiveFormats );
		$this->movePage( $title, $flowArchiveTitle, 'disabling Flow beta feature' );

		$archivedTalkpage = $this->findLatestArchive( $title );
		if ( $archivedTalkpage ) {
			// restore the previously archived wikitext talk page
			$this->movePage( $archivedTalkpage, $title, 'Restoring old talk page' );
		} else {
			// create a rev when that was no talkpage to restore
			$this->createRevision( $title, $flowTalkPageManagerUser, "Your Flow page was archived at [[$flowArchiveTitle]]", "this is a comment" );
		}
	}

	private function isAFlowBoard( Title $title ) {
		return $this->occupationController->isTalkpageOccupied( $title, true );
	}

	private function movePage( Title $from, Title $to, $comment ) {
		$flowTalkPageManagerUser = $this->occupationController->getTalkpageManager();

		$mp = new MovePage( $from, $to );
		$mp->move( $flowTalkPageManagerUser, $comment, false );

		// this was there in the previous version, don't know if it's needed
//		wfWaitForSlaves();
	}

	private function fatal( $msgKey, $args = null ) {
		throw new ImportException( wfMessage( $msgKey, $args )->text() );
	}

	private function from_csv( $str ) {
		return strpos( $str, "\n") === false ? array( $str ) : explode( "\n", $str );
	}

	private function findLatestArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return Converter::findLatestArchiveTitle( $title, $archiveFormats );
	}

	private function findNextArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-page-name-format' )->inContentLanguage()->plain() );
		return Converter::decideArchiveTitle( $title, $archiveFormats );
	}

	private function findLatestFlowArchive( Title $title ) {
		$archiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		return Converter::findLatestArchiveTitle( $title, $archiveFormats );
	}

	private function createRevision( Title $title, User $user, $contentText, $comment ) {
		$page = WikiPage::factory( $title );
		$newContent = new WikitextContent( $contentText );
		$status = $page->doEditContent(
			$newContent,
			$comment,
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$user
		);

		if ( !$status->isGood() ) {
			throw new ImportException( "Failed creating revision at {$title}" );
		}
	}

	/**
	 * @param $title
	 * @param $boardDescription
	 * @throws ImportException
	 */
	private function createFlowBoard( Title $title, $boardDescription )
	{
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

	private function archiveExistingTalkpage( Title $title )
	{
		$archiveTitle = $this->findNextArchive( $title );
		$this->movePage( $title, $archiveTitle, 'Archiving existing talk page' );
	}

	private function restoreExistingFlowBoard( Title $archivedFlowPage, Title $title )
	{
		$this->movePage( $archivedFlowPage, $title, 'Restoring existing Flow page' );
	}
}