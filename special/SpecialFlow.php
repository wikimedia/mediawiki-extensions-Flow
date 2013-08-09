<?php

use Flow\Block\SummaryBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Model\Definition;
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
		$this->getOutput()->addModules( array('ext.flow.base') );
		
		if ( empty( $subPage ) ) {
			// If no specific article was requested, render the users flow
			throw new \MWException( 'TODO: Redirect to users board?' );
		}

		$this->container = $this->loadContainer();
		$request = $this->getRequest();
		$title = $this->loadTitle( $subPage );
		$action = $request->getVal( 'action', 'view' );
		$workflowId = $request->getVal( 'workflow' );
		$user = $this->getUser();

		$this->getOutput()->setPageTitle( $this->msg( 'flow-specialpage', $title->getPrefixedText() )->text() );

		$definitionRequest = $request->getVal( 'definition', null );
		if ( $definitionRequest !== null ) {
			$definitionRequest = UUID::create( $definitionRequest );
		} else {
			$definitionRequest = $request->getVal( 'flow', null );
		}

		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( $title, UUID::create( $workflowId ) );

		$workflow = $this->loader->getWorkflow();
		$definition = $this->loader->getDefinition();

		$blocks = $this->loader->createBlocks();
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}

		if ( $request->getMethod() === 'POST' ) {
			$user = $this->container['user'];
			if ( $request->getVal('wpEditToken') != $user->getEditToken('flow') ) {
				$error = '<div class="error">' . wfMessage('sessionfailure') . '</div>';
				$this->getOutput()->addHTML( $error );
			} else {
				$user = $this->container['user'];
				$request = $this->getRequest();
				$blocksToCommit = $this->loader->handleSubmit( $action, $blocks, $user, $request );
				if ( $blocksToCommit ) {
					$this->loader->commit( $workflow, $blocksToCommit );
					$this->redirect( $workflow, 'view' );
					return;
				}
			}
		}

		$templating = $this->container['templating'];
		$workflowId = $workflow->getId()->getHex();
		$this->getOutput()->addHTML( Html::openElement( 'div',
			array(
				'class' => 'flow-container',
				'data-workflow-id' => $workflowId,
				'data-page-title' => $title->getPrefixedText(),
				'data-workflow-existence' => $workflow->isNew() ? 'new' : 'existing',
			)
		) );
		foreach ( $blocks as $block ) {
			$block->render( $templating, $request->getArray( $block->getName(), array() ) );
		}
		$this->getOutput()->addHTML( "</div>" );
	}

	protected function loadContainer() {
		$container = include __DIR__ . '/../container.php';
		$container['request'] = $this->getRequest();
		$container['output'] = $this->getOutput();

		return $container;
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

	protected function redirect( Workflow $workflow, $action = 'view', array $query = array() ) {
		$url = $this->container['url_generator']->generateUrl( $workflow, $action, $query );
		$this->getOutput()->redirect( $url );
	}
}
