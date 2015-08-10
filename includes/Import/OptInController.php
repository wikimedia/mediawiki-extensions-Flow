<?php

namespace Flow\Import;

use Flow\OccupationController;
use Flow\WorkflowLoaderFactory;
use MovePage;
use RequestContext;
use Title;
use Flow\Container;
use Flow\Import\EnableFlow\EnableFlowWikitextConversionStrategy;
use MWExceptionHandler;
use User;


/**
 * Entry point for enabling Flow on a page.
 */
class OptInController {

	/**
	 * @param User $user
	 * @param Title $title
	 * @param string $boardDescription
	 * @return bool
	 * @throws ImportException
	 */
	public function enable( User $user, Title $title, $boardDescription ) {

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

			$allowCreationStatus = $occupationController->allowCreation( $title, $user, false );
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

		return true;

	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @return bool
	 */
	public function disable( User $user, Title $title ) {
		// move the Flow page to an archive
		$mp = new MovePage( $title, $title->getSubpage( 'Flow_archive_1' ) );
		$mp->move( $user, 'disabling Flow beta feature', false );

		// restore the previously archived wikitext talk page

		return true;
	}

	private function fatal( $msgKey, $args = null ) {
		throw new ImportException( wfMessage( $msgKey, $args )->text() );
	}
}
