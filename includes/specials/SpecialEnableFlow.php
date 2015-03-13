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
	 * @var WorkflowLoaderFactory $loaderFactory
	 */
	protected $loaderFactory;

	/** @var Flow\TalkpageManager $controller */
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
	 * Check that Flow board does not exist, then create it
	 *
	 * @param array $data Form data
	 * @return Status Status indicating result
	 */
	public function onSubmit( array $data ) {
		$page = $data['page'];
		$title = Title::newFromText( $page );

		// Canonicalize so the error or confirmation message looks nicer (no underscores).
		$page = $title->getPrefixedText();

		if ( $this->occupationController->isTalkpageOccupied( $title, true ) ) {
			return Status::newFatal( 'flow-special-enableflow-board-already-exists', $page );
		}

		// This also *records* that it's allowed.
		if ( !$this->occupationController->isCreationAllowed( $title, $this->getUser() ) ) {
			// This is the only plausible reason this method would return false here.
			// If there is another possible reason, we should have the method return a
			// Status.
			return Status::newFatal( 'flow-special-enableflow-page-already-exists', $page );
		}

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

		$status = Status::newGood();

		foreach( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach( $errors as $errorKey ) {
					$status->fatal( $block->getErrorMessage( $errorKey ) );
				}
			}
		}

		$commitMetadata = $loader->commit( $blocksToCommit );

		$this->page = $data['page'];
		return $status;
	}

	public function onSuccess() {
		$confirmationMessage = $this->msg( 'flow-special-enableflow-confirmation', $this->page )->parse();
		$this->getOutput()->addHTML( $confirmationMessage );
	}
}
