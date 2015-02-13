<?php

namespace Flow\Actions;

use Action;
use Article;
use ErrorPageError;
use Flow\Container;
use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\View;
use Flow\WorkflowLoaderFactory;
use IContextSource;
use OutputPage;
use Page;
use Title;
use WebRequest;
use WikiPage;

class FlowAction extends Action {
	/**
	 * @var string
	 */
	protected $actionName;

	/**
	 * @param Page $page
	 * @param IContextSource $source
	 * @param string $actionName
	 */
	public function __construct( Page $page, IContextSource $source, $actionName ) {
		parent::__construct( $page, $source );
		$this->actionName = $actionName;
	}

	/**
	 * @return string
	 */
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
		$occupationController = \FlowHooks::getOccupationController();

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
			$container['url_generator'],
			$container['lightncandy'],
			$output
		);

		$request = $this->context->getRequest();

		// BC for urls pre july 2014 with workflow query parameter
		$redirect = $this->getRedirectUrl( $request, $title );
		if ( $redirect ) {
			$output->redirect( $redirect );
			return;
		}

		$action = $request->getVal( 'action', 'view' );
		try {
			/** @var WorkflowLoaderFactory $factory */
			$factory = $container['factory.loader.workflow'];
			$loader = $factory->createWorkflowLoader( $title );

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


	/**
	 * Flow used to output some permalink urls with workflow ids in them. Each
	 * workflow now has its own page, so those have been deprecated. This checks
	 * a web request for the old workflow parameter and returns a url to redirect
	 * to if necessary.
	 *
	 * @param WebRequest $request
	 * @param Title $title
	 * @return string URL to redirect to or blank string for no redirect
	 */
	protected function getRedirectUrl( WebRequest $request, Title $title ) {
		$workflowId = UUID::create( strtolower( $request->getVal( 'workflow' ) ) ?: null );
		if ( !$workflowId ) {
			return '';
		}

		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		/** @var Workflow $workflow */
		$workflow = $storage->get( 'Workflow', $workflowId );

		// The uuid points to a non-existant workflow
		if ( !$workflow ) {
			return '';
		}

		// The uuid points to the current page
		$redirTitle = $workflow->getArticleTitle();
		if ( $redirTitle->equals( $title ) ) {
			return '';
		}

		// We need to redirect
		return $redirTitle->getLinkURL();
	}
}
