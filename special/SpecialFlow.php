<?php

use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoader;

/**
 * SpecialFlow is intended to bootstrap flow.  It sets up the generic parts of flow that apply
 * to everything, figures out which title/workflow/etc is being requested, and then passes control
 * off to a controller specifically able to handle that type of workflow.
 */

class SpecialFlow extends SpecialPage {

	protected $action;

	public function __construct() {
		parent::__construct( 'Flow' );
	}

	public function execute( $subPage ) {
		$this->setHeaders();

		if ( empty( $subPage ) ) {
			// If no specific article was requested, render the users flow
			throw new \MWException( 'TODO: Redirect to users board?' );
		}

		$container = Flow\Container::getContainer();
		$request = $this->getRequest();
		$title = $this->loadTitle( $subPage );
		$workflowId = $request->getVal( 'workflow' );
		$action = $request->getVal( 'action', 'view' );

		$loader = $container['factory.loader.workflow']
			->createWorkflowLoader( $title, UUID::create( $workflowId ) );

		$view = new Flow\View(
			$container['templating'],
			$container['url_generator'],
			$this->getContext()
		);

		$view->show( $loader, $action );
	}

	protected function loadTitle( $text ) {
		$title = Title::newFromText( $text );
		if ( $title === null ) {
			throw new MWException( 'Invalid article requested' );
		}
		if ( $title->mInterwiki ) {
			throw new MWException( 'Interwiki not implemented yet' );
		}

		return $title;
	}
}
