<?php

namespace Flow;

use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Exception\CrossWikiException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidTopicUuidException;
use Flow\Exception\UnknownWorkflowIdException;
use Title;

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
	 * @var bool
	 */
	protected $pageMoveInProgress = false;

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

	public function pageMoveInProgress() {
		$this->pageMoveInProgress = true;
	}

	/**
	 * @param Title $pageTitle
	 * @param UUID|null $workflowId
	 * @return WorkflowLoader
	 * @throws InvalidInputException
	 * @throws CrossWikiException
	 */
	public function createWorkflowLoader( Title $pageTitle, $workflowId = null ) {
		if ( $pageTitle === null ) {
			throw new InvalidInputException( 'Invalid article requested', 'invalid-title' );
		}

		if ( $pageTitle && $pageTitle->isExternal() ) {
			throw new CrossWikiException( 'Interwiki to ' . $pageTitle->getInterwiki() . ' not implemented ', 'default' );
		}

		if ( $pageTitle->getNamespace() === NS_TOPIC ) {
			$workflowId = self::uuidFromTitle( $pageTitle );
		}
		if ( $workflowId !== null ) {
			$workflow = $this->loadWorkflowById( $pageTitle, $workflowId );
		} else {
			$workflow = $this->loadWorkflow( $pageTitle );
		}

		return new WorkflowLoader(
			$workflow,
			$this->blockFactory->createBlocks( $workflow ),
			$this->submissionHandler
		);
	}

	/**
	 * @param Title $title
	 * @return Workflow
	 * @throws InvalidDataException
	 */
	protected function loadWorkflow( \Title $title ) {
		$storage = $this->storage->getStorage( 'Workflow' );

		// board doesn't currently exist
		if ( $title->getArticleID() === 0 ) {
			return Workflow::create( $this->defaultWorkflowName, $title );
		}

		$found = $storage->find( array(
			'workflow_type' => $this->defaultWorkflowName,
			'workflow_wiki' => $title->isLocal() ? wfWikiId() : $title->getTransWikiID(),
			'workflow_page_id' => $title->getArticleID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
		) );
		if ( $found ) {
			$workflow = reset( $found );
		} else {
			// workflow for this board couldn't be found
			$workflow = Workflow::create( $this->defaultWorkflowName, $title );
		}

		return $workflow;
	}

	/**
	 * @param Title|false $title
	 * @param UUID $workflowId
	 * @return Workflow
	 * @throws InvalidInputException
	 */
	protected function loadWorkflowById( /* Title or false */ $title, $workflowId ) {
		/** @var Workflow $workflow */
		$workflow = $this->storage->getStorage( 'Workflow' )->get( $workflowId );
		if ( !$workflow ) {
			throw new UnknownWorkflowIdException( 'Invalid workflow requested by id', 'invalid-input' );
		}
		if ( $title !== false && $this->pageMoveInProgress === false && !$workflow->matchesTitle( $title ) ) {
			throw new InvalidInputException( 'Flow workflow is for different page', 'invalid-input' );
		}

		return $workflow;
	}

	/**
	 * Create a UUID for a Title object
	 *
	 * @param Title $title
	 * @return UUID
	 * @throws InvalidInputException When the Title does not represent a valid uuid
	 */
	public static function uuidFromTitle( Title $title ) {
		return self::uuidFromTitlePair( $title->getNamespace(), $title->getDbKey() );
	}

	/**
	 * Create a UUID for a ns/dbkey title pair
	 *
	 * @param integer $ns
	 * @param string $dbKey
	 * @return UUID
	 * @throws InvalidInputException When the pair does not represent a valid uuid
	 */
	public static function uuidFromTitlePair( $ns, $dbKey ) {
		if ( $ns !== NS_TOPIC ) {
			throw new InvalidInputException( "Title is not from NS_TOPIC: $ns", 'invalid-input' );
		}

		try {
			return UUID::create( strtolower( $dbKey ) );
		} catch ( InvalidInputException $e ) {
			throw new InvalidTopicUuidException( "$dbKey is not a valid UUID", 0, $e );
		}
	}
}
