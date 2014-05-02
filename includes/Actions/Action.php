<?php

namespace Flow\Actions;

use Action;
use Article;
use ErrorPageError;
use Flow\Container;
use Flow\Model\UUID;
use Flow\View;
use IContextSource;
use Page;
use WikiPage;

class FlowAction extends Action {
	protected $actionName;

	function __construct( Page $page, IContextSource $source, /* string */ $actionName ) {
		parent::__construct( $page, $source );
		$this->actionName = $actionName;
	}

	public function getName() {
		return $this->actionName;
	}

	public function show() {
		$this->showForAction( $this->getName() );
	}

	public function execute() {
		$childContext = new DerivativeContext( RequestContext::getMain() );
		$childContext->setOutput( new OutputPage( $childContext ) );

		$this->showForAction( $this->getName(), $childContext->getOutput() );
	}

	public function showForAction( $action, $output = false ) {
		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];

		if ( $output === false ) {
			$output = $this->context->getOutput();
		}

		// Check if this is actually a Flow page.
		if ( ! $this->page instanceof WikiPage && ! $this->page instanceof Article ) {
			throw new ErrorPageError( 'nosuchaction', 'flow-action-unsupported' );
		} elseif ( ! $occupationController->isTalkpageOccupied( $this->page->getTitle() ) ) {
			throw new ErrorPageError( 'nosuchaction', 'flow-action-unsupported' );
		}

		$view = new View(
			$container['templating'],
			$container['url_generator'],
			$container['lightncandy'],
			$output
		);

		try {
			$request = $this->context->getRequest();

			$workflowId = $request->getVal( 'workflow' );
			$action = $request->getVal( 'action', 'view' );

			$loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $this->page->getTitle(), UUID::create( $workflowId ) );

			if ( !$loader->getWorkflow()->isNew() ) {
				// Workflow currently exists, make sure a revision also exists
				$occupationController->ensureFlowRevision( $this->page, $loader->getWorkflow() );
			}

			$view->show( $loader, $action );
		} catch( \Flow\Exception\FlowException $e ) {
			$e->setOutput( $output );
			throw $e;
		}
	}
}