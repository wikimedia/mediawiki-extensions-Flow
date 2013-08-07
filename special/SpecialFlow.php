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
		$this->getOutput()->setPageTitle( $this->msg( 'flow-specialpage' )->text() );
		if ( empty( $subPage ) ) {
			// If no specific article was requested, render the users flow
			throw new \MWException( 'TODO: Redirect to users board?' );
		}

		$this->container = $this->loadContainer();
		$request = $this->getRequest();
		$title = $this->loadTitle( $subPage );
		$action = $request->getVal( 'action', 'view' );
		$workflowId = $request->getVal( 'workflow' );

		$definitionRequest = $request->getVal( 'definition', null );
		if ( $definitionRequest !== null ) {
			$definitionRequest = UUID::create( $definitionRequest );
		} else {
			$definitionRequest = $request->getVal( 'flow', null );
		}

		$this->loader = new WorkflowLoader( $title, UUID::create( $workflowId ) );

		$workflow = $this->loader->getWorkflow();
		$definition = $this->loader->getDefinition();

		$blocks = $this->loader->createBlocks();
		foreach ( $blocks as $block ) {
			$block->init( $action );
		}

		if ( $request->getMethod() === 'POST' ) {
			$blocksToCommit = $this->handleSubmit( $workflow, $definition, $action, $blocks );
			if ( $blocksToCommit ) {
				$this->commit( $workflow, $blocksToCommit );
				$this->redirect( $workflow, 'view' );
				return;
			}
		}

		$templating = $this->container['templating'];
		foreach ( $blocks as $block ) {
			$block->render( $templating, $request->getArray( $block->getName(), array() ) );
		}
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

	protected function handleSubmit( Workflow $workflow, Definition $definition, $action, array $blocks ) {

		$request = $this->getRequest();
		$user = $this->container['user'];
		$success = true;
		$interestedBlocks = array();

		foreach ( $blocks as $block ) {
			$data = $request->getArray( $block->getName(), array() );
			$result = $block->onSubmit( $action, $user, $data );
			if ( $result !== null ) {
				$interestedBlocks[] = $block;
				$success &= $result;
			}
		}
		if ( !$interestedBlocks ) {
			if ( !$blocks ) {
				throw new \MWException( 'No Blocks?!?' );
			}
			$type = array();
			foreach ( $blocks as $block ) {
				$type[] = get_class( $block );
			}
			// All blocks returned null, nothing knows how to handle this action
			throw new \MWException( "No block accepted the '$action' action: " .  implode( ',', array_unique( $type ) ) );
		}
		return $success ? $interestedBlocks : array();
	}

	public function commit( Workflow $workflow, array $blocks ) {
		$this->container['storage.workflow']->put( $workflow );
		foreach ( $blocks as $block ) {
			$block->commit();
		}
	}

	protected function redirect( Workflow $workflow, $action = 'view', array $query = array() ) {
		$url = $this->container['url_generator']->generateUrl( $workflow, $action, $query );
		$this->getOutput()->redirect( $url );
	}
}
