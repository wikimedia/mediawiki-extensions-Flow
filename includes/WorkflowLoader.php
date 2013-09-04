<?php

namespace Flow;

use Flow\Block\SummaryBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Model\Definition;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Data\ObjectStorage;
use Flow\Data\RootPostLoader;

class WorkflowLoader {
	protected $workflow, $definition;

	public function __construct(
			$pageTitle,
			/*UUID or NULL*/ $workflowId,
			$definitionRequest,
			ManagerGroup $storage,
			RootPostLoader $rootPostLoader
	) {
		if ( $pageTitle === null ) {
			throw new \MWException( 'Invalid article requested' );
		}
		if ( $pageTitle && $pageTitle->mInterwiki ) {
			throw new \MWException( 'Interwiki not implemented yet' );
		}
		if ( $pageTitle && $pageTitle->getArticleID() === 0 ) {
			throw new \MWException( 'Can only load workflows for existing page. Page '.( $pageTitle->getPrefixedText() ). ' does not exist.' );
		}

		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;

		$this->definitionRequest = $definitionRequest;

		$workflow = null;

		if ( $workflowId !== null ) {
			list( $workflow, $definition ) = $this->loadWorkflowById( $pageTitle, $workflowId );
		} else {
			list( $workflow, $definition ) = $this->loadWorkflow( $pageTitle );
		}

		if ( ! $workflow || ! $definition ) {
			throw new \MWException( "Unable to load workflow and definition" );
		}

		$this->workflow = $workflow;
		$this->definition = $definition;
	}

	public function getDefinition() {
		return $this->definition;
	}

	public function getWorkflow() {
		return $this->workflow;
	}

	protected function loadWorkflow( \Title $title ) {
		global $wgContLang, $wgUser;
		$storage = $this->storage->getStorage( 'Workflow');

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

	protected function loadWorkflowById( /* Title or false */ $title, $workflowId ) {
		$workflow = $this->storage->getStorage( 'Workflow' )->get( $workflowId );
		if ( !$workflow ) {
			throw new \MWException( 'Invalid workflow requested by id' );
		}
		if ( $title !== false && !$workflow->matchesTitle( $title ) ) {
			// todo: redirect?
			throw new \MWException( 'Flow workflow is for different page' );
		}
		$definition = $this->storage->getStorage( 'Definition' )->get( $workflow->getDefinitionId() );
		if ( !$definition ) {
			throw new \MWException( 'Flow workflow references unknown definition id: ' . $workflow->getDefinitionId()->getHex() );
		}

		return array( $workflow, $definition );
	}

	protected function loadDefinition() {
		global $wgFlowDefaultWorkflow;

		$repo = $this->storage->getStorage( 'Definition' );
		$id = $this->definitionRequest;
		if ( $id instanceof UUID ) {
			$definition = $repo->get( $id );
			if ( $definition === null ) {
				throw new MWException( "Unknown flow id '$id' requested" );
			}
		} else {
			$workflowName = $id ? $id : $wgFlowDefaultWorkflow;
			$found = $repo->find( array(
				'definition_name' => strtolower( $workflowName ),
				'definition_wiki' => wfWikiId(),
			) );
			if ( $found ) {
				$definition = reset( $found );
			} else {
				throw new \MWException( "Unknown flow type '$workflowName' requested" );
			}
		}
		return $definition;
	}

	public function createBlocks( ) {
		switch( $this->definition->getType() ) {
		case 'discussion':
			return array(
				'summary' => new SummaryBlock( $this->workflow, $this->storage ),
				'topics' => new TopicListBlock( $this->workflow, $this->storage, $this->rootPostLoader ),
			);

		case 'topic':
			return array(
				'topic' => new TopicBlock( $this->workflow, $this->storage, $this->rootPostLoader ),
			);

		default:
			throw new \MWException( 'Not Implemented' );
		}
	}

	public function handleSubmit( $action, array $blocks, $user, \WebRequest $request ) {
		$success = true;
		$interestedBlocks = array();
		$workflow = $this->getWorkflow();

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
		$this->storage->getStorage( 'Workflow' )->put( $workflow );
		$results = array();
		foreach ( $blocks as $block ) {
			$results[$block->getName()] = $block->commit();
		}

		return $results;
	}

}

class WorkflowLoaderFactory {
	protected $storage, $rootPostLoader;

	function __construct( ManagerGroup $storage, RootPostLoader $rootPostLoader ) {
		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;
	}

	public function createWorkflowLoader( $pageTitle, $workflowId = null, $definitionRequest = false ) {
		return new WorkflowLoader(
			$pageTitle,
			$workflowId,
			$definitionRequest,
			$this->storage,
			$this->rootPostLoader
		);
	}
}
