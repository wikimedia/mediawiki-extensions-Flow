<?php

namespace Flow\Import;

use Flow\OccupationController;
use Flow\WorkflowLoaderFactory;
use MovePage;
use RequestContext;
use Revision;
use Title;
use Flow\Container;
use Flow\Import\EnableFlow\EnableFlowWikitextConversionStrategy;
use MWExceptionHandler;
use User;
use WikiPage;
use WikitextContent;


/**
 * Entry point for enabling Flow on a page.
 */
class OptInController {

	/**
	 * @param User $user
	 * @throws ImportException
	 */
	public function enable( User $user ) {
		$boardDescription = 'Welcome to Flow';
		$title = $user->getTalkPage();

		/** @var WorkflowLoaderFactory $loaderFactory */
		$loaderFactory = Container::get( 'factory.loader.workflow' );

		/** @var OccupationController $occupationController */
		$occupationController = Container::get( 'occupation_controller' );

		// Canonicalize so the error or confirmation message looks nicer (no underscores)
		$page = $title->getPrefixedText();

		if ( $occupationController->isTalkpageOccupied( $title, true ) ) {
			$this->fatal( 'flow-special-enableflow-board-already-exists', $page );
		}

		if ( $title->exists( Title::GAID_FOR_UPDATE ) ) {

			if ( class_exists( 'LqtDispatch' ) && \LqtDispatch::isLqtPage( $title ) ) {
				$this->fatal( 'flow-special-enableflow-page-is-liquidthreads', $page );
			}

			$logger = Container::get( 'default_logger' );

			$converter = new Converter(
				wfGetDB( DB_MASTER ),
				Container::get( 'importer' ),
				$logger,
				$occupationController->getTalkpageManager(),
				new EnableFlowWikitextConversionStrategy(
					Container::get( 'parser' ),
					new NullImportSourceStore(),
					$logger,
					array(),
					$boardDescription
				)
			);

			try {
				$converter->convert( $title );
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->fatal( 'flow-error-external', $e->getMessage() );
			}

		} else {

			if ( !$boardDescription ) {
				$this->fatal( 'flow-special-enableflow-non-existent-requires-description' );
			}

			$asUser = $occupationController->getTalkpageManager();
			$allowCreationStatus = $occupationController->allowCreation( $title, $asUser, false );
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

			foreach( $blocks as $block ) {
				if ( $block->hasErrors() ) {
					$errors = $block->getErrors();

					foreach( $errors as $errorKey ) {
						$this->fatal( $block->getErrorMessage( $errorKey ) );
					}
				}
			}

			$loader->commit( $blocksToCommit );
		}
	}

	/**
	 * @param User $user
	 */
	public function disable( User $user ) {
		/** @var OccupationController $occupationController */
		$occupationController = Container::get( 'occupation_controller' );
		$flowTalkPageManagerUser = $occupationController->getTalkpageManager();

		$title = $user->getTalkPage();

		// move the Flow page to an archive
		$flowArchiveFormats = $this->from_csv(
			wfMessage( 'flow-conversion-archive-flow-page-name-format' )->inContentLanguage()->plain() );
		$flowArchiveTitle = Converter::decideArchiveTitle( $title, $flowArchiveFormats );
		$mp = new MovePage( $title, $flowArchiveTitle );
		$mp->move( $flowTalkPageManagerUser, 'disabling Flow beta feature', true );

		$archivedTalkpage = $this->findLatestArchive( $title );
		if ( $archivedTalkpage ) {
			// restore the previously archived wikitext talk page
			$mp = new MovePage( $archivedTalkpage, $title );
			$mp->move( $flowTalkPageManagerUser, 'Restoring old talk page', false );
		} else {
			// create a rev when that was no talkpage to restore
			$this->createRevision( $title, $flowTalkPageManagerUser, "Your Flow page was archived at [[$flowArchiveTitle]]", "this is a comment" );
		}
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
			throw new ImportException( "Failed creating archive cleanup revision at {$title}" );
		}
	}
}