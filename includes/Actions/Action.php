<?php

namespace Flow\Actions;

use Action;
use Article;
use ErrorPageError;
use Flow\Container;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Model\UUID;
use Flow\View;
use IContextSource;
use OutputPage;
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

	/**
	 * @param string $action
	 * @param OutputPage|null $output
	 * @throws ErrorPageError
	 * @throws FlowException
	 */
	public function showForAction( $action, OutputPage $output = null ) {
		$container = Container::getContainer();
		$occupationController = $container['occupation_controller'];

		if ( $output === null ) {
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
		} catch ( InvalidInputException $e ) {
			if ( $workflowId ) {
				// Check if it's the wrong title, redirect to correct one
				$storage = $container['storage'];
				$workflow = $storage->get( 'Workflow', $workflowId );

				if (
					$workflow &&
					! $workflow->getArticleTitle()->equals( $title )
				) {
					$redirTitle = $workflow->getArticleTitle();
					$query = array( 'workflow' => $workflowId->getAlphadecimal() );
					$redirUrl = $redirTitle->getLinkURL( $query );

					$output->redirect( $redirUrl );
					return;
				}
			}

			// If we couldn't handle it by redirecting, show an error
			$e->setOutput( $output );
			throw $e;
		} catch( FlowException $e ) {
			$e->setOutput( $output );
			throw $e;
		}
	}

	protected function detectWorkflowId( Title $title, WebRequest $request ) {
		if ( $title->getNamespace() === NS_TOPIC ) {
			$uuid = UUID::create( strtolower( $title->getText() ) );
			if ( !$uuid ) {
				// @todo better error handling
				throw new FlowException( 'Invalid title: not a uuid' );
			}
		} else {
			$uuid = UUID::create( strtolower( $request->getVal( 'workflow' ) ) ?: null );
		}

		return $uuid;
	}
}
