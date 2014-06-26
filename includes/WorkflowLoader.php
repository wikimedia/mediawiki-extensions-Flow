<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Block\HeaderBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Block\TopicSummaryBlock;
use Flow\Block\BoardHistoryBlock;
use Flow\Model\Definition;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\Exception\CrossWikiException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidActionException;
use Title;
use WebRequest;

class WorkflowLoader {
	/**
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @var Definition
	 */
	protected $definition;

	/**
	 * @var BlockFactory
	 */
	protected $blockFactory;

	/**
	 * @var SubmissionHandler
	 */
	protected $submissionHandler;

	/**
	 * @param Definition $definiton
	 * @param Workflow $workflow
	 * @param BlockFactory $blockFactory
	 * @param SubmissionHandler $submissionHandler
	 */
	public function __construct(
			Definition $definition,
			Workflow $workflow,
			BlockFactory $blockFactory,
			SubmissionHandler $submissionHandler
	) {
		$this->blockFactory = $blockFactory;
		$this->submissionHandler = $submissionHandler;
		$this->definition = $definition;
		$this->workflow = $workflow;
	}

	/**
	 * @return Definition
	 */
	public function getDefinition() {
		return $this->definition;
	}

	/**
	 * @return Workflow
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * @return AbstractBlock[]
	 */
	public function createBlocks() {
		return $this->blockFactory->createBlocks( $this->definition, $this->workflow );
	}

	public function handleSubmit( $action, array $blocks, $user, WebRequest $request ) {
		return $this->submissionHandler->handleSubmit( $this->workflow, $action, $blocks, $user, $request );
	}

	public function commit( Workflow $workflow, array $blocks ) {
		return $this->submissionHandler->commit( $workflow, $blocks );
	}

	public function extractBlockParameters( WebRequest $request, array $blocks ) {
		return $this->submissionHandler->extractBlockParameters( $request, $blocks );
	}
}

class BlockFactory {
	public function __construct(
		ManagerGroup $storage,
		NotificationController $notificationController,
		RootPostLoader $rootPostLoader
	) {
		$this->storage = $storage;
		$this->notificationController = $notificationController;
		$this->rootPostLoader = $rootPostLoader;
	}

	/**
	 * @return AbstractBlock[]
	 * @throws InvalidInputException When the definition type is unrecognized
	 * @throws InvalidDataException When multiple blocks share the same name
	 */
	public function createBlocks( Definition $definition, Workflow $workflow ) {
		switch( $definition->getType() ) {
			case 'discussion':
				$blocks = array(
					new HeaderBlock( $workflow, $this->storage, $this->notificationController ),
					new TopicListBlock( $workflow, $this->storage, $this->notificationController ),
					new BoardHistoryBlock( $workflow, $this->storage, $this->notificationController ),
				);
				break;

			case 'topic':
				$blocks = array(
					new TopicBlock( $workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
					new TopicSummaryBlock( $workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
				);
				break;

			default:
				throw new InvalidInputException( 'Not Implemented', 'invalid-definition' );
				break;
		}

		$return = array();
		/** @var AbstractBlock[] $blocks */
		foreach ( $blocks as $block ) {
			if ( !isset( $return[$block->getName()] ) ) {
				$return[$block->getName()] = $block;
			} else {
				throw new InvalidDataException( 'Multiple blocks with same name is not yet supported', 'fail-load-data' );
			}
		}

		return $return;
	}
}

class SubmissionHandler {

	public function __construct( ManagerGroup $storage, DbFactory $dbFactory, BufferedCache $bufferedCache ) {
		$this->storage = $storage;
		$this->dbFactory = $dbFactory;
		$this->bufferedCache = $bufferedCache;
	}

	/**
	 * @param string $action
	 * @param AbstractBlock[] $blocks
	 * @param \User $user
	 * @param WebRequest $request
	 * @return AbstractBlock[]
	 * @throws InvalidActionException
	 * @throws InvalidDataException
	 */
	public function handleSubmit( Workflow $workflow, $action, array $blocks, $user, WebRequest $request ) {
		$success = true;
		$interestedBlocks = array();

		$params = $this->extractBlockParameters( $request, $blocks );
		foreach ( $blocks as $block ) {
			$data = $params[$block->getName()];
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

		// Check permissions before allowing any writes
		if ( $user->isBlocked() ||
			!$workflow->getArticleTitle()->userCan( 'edit', $user )
		) {
			reset( $interestedBlocks )->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			$success = false;
		}

		return $success ? $interestedBlocks : array();
	}

	/**
	 * @param Workflow $workflow
	 * @param AbstractBlock[] $blocks
	 * @return array
	 * @throws \Exception
	 */
	public function commit( Workflow $workflow, array $blocks ) {
		$cache = $this->bufferedCache;
		$dbw = $this->dbFactory->getDB( DB_MASTER );

		try {
			$dbw->begin();
			$cache->begin();
			// @todo doesn't feel right to have this here
			$this->storage->getStorage( 'Workflow' )->put( $workflow );
			$results = array();
			foreach ( $blocks as $block ) {
				$results[$block->getName()] = $block->commit();
			}
			$dbw->commit();
		} catch ( \Exception $e ) {
			$dbw->rollback();
			$cache->rollback();
			throw $e;
		}

		try {
			$cache->commit();
		} catch ( \Exception $e ) {
			wfWarn( __METHOD__ . ': Commited to database but failed applying to cache' );
			\MWExceptionHandler::logException( $e );
		}

		return $results;
	}

	/**
	 * Helper function extracts parameters from a WebRequest.
	 *
	 * @todo this implementation should be deprecated in favor of making
	 * all forms submit the equivilent api parameter names rather than
	 * the current prefixes.
	 *
	 * @param WebRequest $request
	 * @param AbstractBlock[] $blocks
	 * @return array
	 */
	public function extractBlockParameters( WebRequest $request, array $blocks ) {
		$result = array();
		// BC for old parameters enclosed in square brackets
		foreach ( $blocks as $block ) {
			$name = $block->getName();
			$result[$name] = $request->getArray( $name, array() );
		}
		// BC for topic_list renamed to topiclist
		if ( isset( $result['topiclist'] ) && !$result['topiclist'] ) {
			$result['topiclist'] = $request->getArray( 'topic_list', array() );
		}
		// between urls only allowing [-_.] as unencoded special chars and
		// php mangling all of those into '_', we have to split on '_'
		$globalData = array();
		foreach ( $request->getValues() as $name => $value ) {
			if ( false !== strpos( $name, '_' ) ) {
				list( $block, $var ) = explode( '_', $name, 2 );
				// flow_xxx is global data for all blocks
				if ( $block === 'flow' ) {
					$globalData[$var] = $value;
				} else {
					$result[$block][$var] = $value;
				}
			}
		}

		foreach ( $blocks as $block ) {
			$result[$block->getName()] += $globalData;
		}

		return $result;
	}
}

class WorkflowLoaderFactory {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var BlockFactory
	 */
	protected $blockFactory;

	/**
	 * @var SubmissionHandler
	 */
	protected $submissionHandler;

	/**
	 * @var string
	 */
	protected $defaultWorkflowName;

	/**
	 * @param ManagerGroup $storage
	 * @param BlockFactory $blockFactory
	 * @param SubmissionHandler $submissionHandler
	 * @param string $defaultWorkflowName
	 */
	function __construct(
		ManagerGroup $storage,
		BlockFactory $blockFactory,
		SubmissionHandler $submissionHandler,
		$defaultWorkflowName
	) {
		$this->storage = $storage;
		$this->blockFactory = $blockFactory;
		$this->submissionHandler = $submissionHandler;
		$this->defaultWorkflowName = $defaultWorkflowName;
	}

	/**
	 * @param string $pageTitle
	 * @param UUID|null $workflowId
	 * @param string|false $definitionRequest
	 * @return WorkflowLoader
	 */
	public function createWorkflowLoader( $pageTitle, $workflowId = null, $definitionRequest = false ) {
		if ( $pageTitle === null ) {
			throw new InvalidInputException( 'Invalid article requested', 'invalid-title' );
		}

		if ( $pageTitle && $pageTitle->isExternal() ) {
			throw new CrossWikiException( 'Interwiki to ' . $pageTitle->getInterwiki() . ' not implemented ', 'default' );
		}

		// @todo constructors should just do simple setup, this goes out and hits the database
		if ( $workflowId !== null ) {
			list( $workflow, $definition ) = $this->loadWorkflowById( $pageTitle, $workflowId );
		} else {
			list( $workflow, $definition ) = $this->loadWorkflow( $pageTitle, $definitionRequest );
		}

		return new WorkflowLoader(
			$definition,
			$workflow,
			$this->blockFactory,
			$this->submissionHandler
		);
	}

	/**
	 * @param Title $title
	 * @param string $definitionRequest
	 * @return array [Workflow, Definition]
	 * @throws InvalidDataException
	 */
	protected function loadWorkflow( \Title $title, $definitionRequest ) {
		global $wgUser;
		$storage = $this->storage->getStorage( 'Workflow');

		$definition = $this->loadDefinition( $definitionRequest );
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

	/**
	 * @param Titkle|false $title
	 * @param string $workflowId
	 * @return array [Workflow, Definition]
	 * @throws InvalidInputException
	 */
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
			throw new InvalidInputException( 'Flow workflow references unknown definition id: ' . $workflow->getDefinitionId()->getAlphadecimal(), 'invalid-input' );
		}

		return array( $workflow, $definition );
	}

	/**
	 * @parma string $id
	 * @return Definition
	 */
	protected function loadDefinition( $id ) {
		global $wgFlowDefaultWorkflow;

		$repo = $this->storage->getStorage( 'Definition' );
		if ( $id instanceof UUID ) {
			$definition = $repo->get( $id );
			if ( $definition === null ) {
				throw new InvalidInputException( "Unknown flow id '$id' requested", 'invalid-input' );
			}
		} else {
			$workflowName = $id ? $id : $this->defaultWorkflowName;
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

}

