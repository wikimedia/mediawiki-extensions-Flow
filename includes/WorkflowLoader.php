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
use Flow\Exception\InvalidInputException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidActionException;

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
			throw new InvalidInputException( 'Invalid article requested', 'invalid-title' );
		}

		if ( $pageTitle && $pageTitle->mInterwiki ) {
			throw new FlowException( 'Interwiki not implemented yet', 'default' );
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
			throw new InvalidDataException( 'Unable to load workflow and definition', 'fail-load-data' );
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
			throw new InvalidDataException( 'Workflow is non-unique, can only fetch object by title + id', 'fail-load-data' );
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
			throw new InvalidInputException( 'Invalid workflow requested by id', 'invalid-input' );
		}
		if ( $title !== false && !$workflow->matchesTitle( $title ) ) {
			throw new InvalidInputException( 'Flow workflow is for different page', 'invalid-input' );
		}
		$definition = $this->storage->getStorage( 'Definition' )->get( $workflow->getDefinitionId() );
		if ( !$definition ) {
			throw new InvalidInputException( 'Flow workflow references unknown definition id: ' . $workflow->getDefinitionId()->getHex(), 'invalid-input' );
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
				throw new InvalidInputException( "Unknown flow id '$id' requested", 'invalid-input' );
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
				throw new InvalidInputException( "Unknown flow type '$workflowName' requested", 'invalid-input' );
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
			throw new InvalidInputException( 'Not Implemented', 'invalid-definition' );
		}

		$return = array();
		foreach ( $blocks as $block ) {
			if ( !isset( $return[$block->getName()] ) ) {
				$return[$block->getName()] = $block;
			} else {
				throw new InvalidDataException( 'Multiple blocks with same name is not yet supported', 'fail-load-data' );
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
				throw new InvalidDataException( 'No Blocks?!?', 'fail-load-data' );
			}
			$type = array();
			foreach ( $blocks as $block ) {
				$type[] = get_class( $block );
			}
			// All blocks returned null, nothing knows how to handle this action
			throw new InvalidActionException( "No block accepted the '$action' action: " .  implode( ',', array_unique( $type ) ), 'invalid-action' );
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
