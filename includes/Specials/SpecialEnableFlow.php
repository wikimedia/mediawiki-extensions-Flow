<?php

namespace Flow\Specials;

use FormSpecialPage;
use Status;
use Title;
use Flow\Container;

/**
 * A special page that allows users with the flow-create-board right to create
 * boards where there no page exists
 */
class SpecialEnableFlow extends FormSpecialPage {
	/**
	 * @var \Flow\WorkflowLoaderFactory $loaderFactory
	 */
	protected $loaderFactory;

	/** @var \Flow\TalkpageManager $controller */
	protected $occupationController;

	/**
	 * @var string $page Full page name that was converted to a board
	 */
	protected $page;

	public function __construct() {
		parent::__construct( 'EnableFlow', 'flow-create-board' );

		$this->loaderFactory = Container::get( 'factory.loader.workflow' );
		$this->occupationController = Container::get( 'occupation_controller' );
	}

	protected function getFormFields() {
		return array(
			'page' => array(
				'type' => 'text',
				'label-message' => 'flow-special-enableflow-page',
			),
			'archive-title-format' => array(
				'type' => 'text',
				'label-message' => 'flow-special-enableflow-archive-title-format',
				'default' => '%s/Archive_%d',
			),
			'header' => array(
				'type' => 'textarea',
				'label-message' => 'flow-special-enableflow-header'
			),
		);
	}

	protected function getDisplayFormat() {
		return 'vform';
	}

	protected function getMessagePrefix() {
		return 'flow-special-enableflow';
	}

	/**
	 * Creates a flow board.
	 * Archives any pre-existing wikitext talk page.
	 *
	 * @param array $data Form data
	 * @return Status Status indicating result
	 */
	public function onSubmit( array $data ) {
		$page = $data['page'];
		$title = Title::newFromText( $page );
		if ( !$title ) {
			return Status::newFatal( 'flow-special-enableflow-invalid-title', $page );
		}

		// Canonicalize so the error or confirmation message looks nicer (no underscores).
		$page = $title->getPrefixedText();

		if ( $this->occupationController->isTalkpageOccupied( $title, true ) ) {
			return Status::newFatal( 'flow-special-enableflow-board-already-exists', $page );
		}

		if ( !$this->occupationController->allowCreation( $title, $this->getUser(), false ) ) {
			return Status::newFatal( 'flow-special-enableflow-board-creation-not-allowed', $page );
		}

		$status = Status::newGood();

		if ( $title->exists() ) {

			if ( class_exists( 'LqtDispatch' ) && LqtDispatch::isLqtPage( $title ) ) {
				return Status::newFatal( 'flow-special-enableflow-page-is-liquidthreads', $page );
			}

			$converter = new \Flow\Import\Converter(
				wfGetDB( DB_MASTER ),
				Container::get( 'importer' ),
				Container::get( 'default_logger' ),
				$this->occupationController->getTalkpageManager(),
				new \Flow\Import\Wikitext\ConversionStrategy(
					Container::get( 'parser' ),
					new \Flow\Import\NullImportSourceStore(),
					$data['archive-title-format'],
					$data['header']
				)
			);

			$converter->convert( array( $title ) );

		} else {
			$loader = $this->loaderFactory->createWorkflowLoader( $title );
			$blocks = $loader->getBlocks();

			$action = 'edit-header';
			$params = array(
				'header' => array(
					'content' => $data['header'],
					'format' => 'wikitext',
				),
			);

			$blocksToCommit = $loader->handleSubmit(
				$this->getContext(),
				$action,
				$params
			);

			foreach( $blocks as $block ) {
				if ( $block->hasErrors() ) {
					$errors = $block->getErrors();

					foreach( $errors as $errorKey ) {
						$status->fatal( $block->getErrorMessage( $errorKey ) );
					}
				}
			}

			$loader->commit( $blocksToCommit );
		}

		$this->page = $data['page'];
		return $status;
	}

	public function onSuccess() {
		$confirmationMessage = $this->msg( 'flow-special-enableflow-confirmation', $this->page )->parse();
		$this->getOutput()->addHTML( $confirmationMessage );
	}
}
