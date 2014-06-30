<?php

namespace Flow\Actions;

use Action;
use Article;
use ErrorPageError;
use Flow\Container;
use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\View;
use IContextSource;
use Page;
use Title;
use WebRequest;
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
		}

		$title = $this->page->getTitle();
		if ( ! $occupationController->isTalkpageOccupied( $title ) ) {
			throw new ErrorPageError( 'nosuchaction', 'flow-action-unsupported' );
		}

		// @todo much of this seems to duplicate BoardContent::getParserOutput
		$view = new View(
			$container['templating'],
			$container['url_generator'],
			$container['lightncandy'],
			$output
		);

		$request = $this->context->getRequest();

		$workflowId = $this->detectWorkflowId( $title, $request );
		$action = $request->getVal( 'action', 'view' );

		try {
			$loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $title, UUID::create( $workflowId ) );

			if ( $title->getNamespace() === NS_TOPIC && $loader->getWorkflow()->getType() !== 'topic' ) {
				// @todo better error handling
				throw new FlowException( 'Invalid title: uuid is not a topic' );
			}

			if ( !$loader->getWorkflow()->isNew() ) {
				// Workflow currently exists, make sure a revision also exists
				$occupationController->ensureFlowRevision( $this->page, $loader->getWorkflow() );
			}

			$view->show( $loader, $action );
		} catch( FlowException $e ) {
			$e->setOutput( $output );
			throw $e;
		}
	}

	protected function detectWorkflowId( Title $title, WebRequest $request ) {
		if ( $title->getNamespace() === NS_TOPIC ) {
			$uuid = UUID::create( $title->getText() );
			if ( !$uuid ) {
				// @todo better error handling
				throw new FlowException( 'Invalid title: not a uuid' );
			}
		} else {
			$uuid = UUID::create( $request->getVal( 'workflow' ) );
		}

		return $uuid;
	}
}
