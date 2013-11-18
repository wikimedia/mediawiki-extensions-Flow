<?php

namespace Flow;

use Flow\Block\HeaderBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Model\Definition;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\Data\ObjectStorage;
use Flow\Data\RootPostLoader;
use Flow\NotificationController;

class WorkflowLoader {
	protected $dbFactory, $bufferedCache;
	protected $workflow, $definition, $storage, $rootPostLoader, $notificationController, $definitionRequest;

	public function __construct(
			$pageTitle,
			/*UUID or NULL*/ $workflowId,
			$definitionRequest,
			DbFactory $dbFactory,
			BufferedCache $bufferedCache,
			ManagerGroup $storage,
			RootPostLoader $rootPostLoader,
			NotificationController $notificationController
	) {
		if ( $pageTitle === null ) {
			throw new \MWException( 'Invalid article requested' );
		}

		if ( $pageTitle && $pageTitle->mInterwiki ) {
			throw new \MWException( 'Interwiki not implemented yet' );
		}

		$this->dbFactory = $dbFactory;
		$this->bufferedCache = $bufferedCache;
		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;
		$this->notificationController = $notificationController;

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
		global $wgUser;
		$storage = $this->storage->getStorage( 'Workflow');

		$definition = $this->loadDefinition();
		if ( !$definition->getOption( 'unique' ) ) {
			throw new \MWException( 'Workflow is non-unique, can only fetch object by title + id' );
		}
		$found = $storage->find( array(
			'workflow_definition_id' => $definition->getId(),
			'workflow_wiki' => $title->isLocal() ? wfWikiId() : $title->getTransWikiID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
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
			$blocks = array(
				new HeaderBlock( $this->workflow, $this->storage, $this->notificationController ),
				new TopicListBlock( $this->workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
			);
			break;

		case 'topic':
			$blocks = array(
				new TopicBlock( $this->workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
			);
			break;

		default:
			throw new \MWException( 'Not Implemented' );
		}

		$return = array();
		foreach ( $blocks as $block ) {
			if ( !isset( $return[$block->getName()] ) ) {
				$return[$block->getName()] = $block;
			} else {
				throw new \MWException( 'Multiple blocks with same name is not yet supported' );
			}
		}

		return $return;
	}

	public function handleSubmit( $action, array $blocks, $user, \WebRequest $request ) {
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
		$cache = $this->bufferedCache;

		try {
			$cache->begin();
			$this->storage->getStorage( 'Workflow' )->put( $workflow );
			$results = array();
			foreach ( $blocks as $block ) {
				$results[$block->getName()] = $block->commit();
			}
			// Delay writing to cache until after db transaction has commited.
			$this->dbFactory->getDB( DB_MASTER )->onTransactionIdle( function() use( $cache ) {
				$cache->commit();
			} );
		} catch ( \Exception $e ) {
			$cache->rollback();
			throw $e;
		}

		return $results;
	}

}

class WorkflowLoaderFactory {
	protected $storage, $rootPostLoader, $notificationController;

	function __construct( DbFactory $dbFactory, BufferedCache $bufferedCache, ManagerGroup $storage, RootPostLoader $rootPostLoader, NotificationController $notificationController ) {
		$this->dbFactory = $dbFactory;
		$this->bufferedCache = $bufferedCache;
		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;
		$this->notificationController = $notificationController;
	}

	public function createWorkflowLoader( $pageTitle, $workflowId = null, $definitionRequest = false ) {
		return new WorkflowLoader(
			$pageTitle,
			$workflowId,
			$definitionRequest,
			$this->dbFactory,
			$this->bufferedCache,
			$this->storage,
			$this->rootPostLoader,
			$this->notificationController
		);
	}
}
