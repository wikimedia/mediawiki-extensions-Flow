<?php

use Flow\Block\SummaryBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Model\Definition;
use Flow\Model\Workflow;

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

		if ( $title->getArticleID() === 0 ) {
			throw new \MWException( 'Can only load workflows for existing page' );
		}
		if ( $workflowId ) {
			list( $workflow, $definition ) = $this->loadWorkflowById( $title, $workflowId );
		} else {
			list( $workflow, $definition ) = $this->loadWorkflow( $title );
		}

		$blocks = $this->createBlocks( $workflow, $definition );
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

	protected function loadDefinition() {
		global $wgFlowDefaultWorkflow;

		$repo = $this->container['storage.definition'];
		$id = $this->getRequest()->getVal( 'definition' );
		if ( $id !== null ) {
			$definition = $repo->get( $id );
			if ( $definition === null ) {
				throw new MWException( "Unknown flow id '$id' requested" );
			}
		} else {
			$workflowName = $this->getRequest()->getVal( 'flow', $wgFlowDefaultWorkflow );
			$found = $repo->find( array(
				'definition_name' => strtolower( $workflowName ),
				'definition_wiki' => wfWikiId(),
			) );
			if ( $found ) {
				$definition = reset( $found );
			}
			if ( empty( $definition ) ) {
				throw new MWException( "Unknown flow type '$workflowName' requested" );
			}
		}
		return $definition;
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


	protected function loadWorkflow( Title $title ) {
		global $wgContLang, $wgUser;
		$storage = $this->container['storage.workflow'];

		$definition = $this->loadDefinition();
		if ( !$definition->getOption( 'unique' ) ) {
			throw new \MWException( 'Workflow is non-unique, can only fetch object by title + id' );
		}
		$found = $storage->find( array(
			'workflow_definition_id' => $definition->getId(),
			'workflow_wiki' => $title->isLocal() ? wfWikiId() : $title->getTransWikiID(),
			'workflow_page_id' => $title->getArticleID(),
		) );
		if ( $found ) {
			$workflow = reset( $found );
		} else {
			$workflow = Workflow::create( $definition, $wgUser, $title );
		}
		return array( $workflow, $definition );
	}

	protected function loadWorkflowById( \Title $title, $workflowId ) {
		$workflow = $this->container['storage.workflow']->get( $workflowId );
		if ( !$workflow ) {
			throw new \MWException( 'Invalid workflow requested by id' );
		}
		if ( !$workflow->matchesTitle( $title ) ) {
			// todo: redirect?
			throw new \MWException( 'Flow workflow is for different page' );
		}
		$definition = $this->container['storage.definition']->get( $workflow->getDefinitionId() );
		if ( !$definition ) {
			throw new \MWException( 'Flow workflow references unknown definition id: ' . $workflow->getDefinitionId() );
		}

		return array( $workflow, $definition );
	}

	protected function createBlocks( Workflow $workflow, Definition $definition ) {
		switch( $definition->getType() ) {
		case 'discussion':
			return array(
				'summary' => new SummaryBlock( $workflow, $this->container['storage'] ),
				'topics' => new TopicListBlock( $workflow, $this->container['storage'], $this->container['loader.root_post'] ),
			);

		case 'topic':
			return array(
				'topic' => new TopicBlock( $workflow, $this->container['storage'], $this->container['loader.root_post'] ),
			);

		default:
			throw new \MWException( 'Not Implemented' );
		}
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
