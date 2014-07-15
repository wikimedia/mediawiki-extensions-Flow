<?php

namespace Flow;

use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Exception\CrossWikiException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\InvalidDataException;
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
	 * @param UUID|string|null $workflowId
	 * @return WorkflowLoader
	 * @throws InvalidInputException
	 * @throws CrossWikiException
	 */
	public function createWorkflowLoader( $pageTitle, $workflowId = null ) {
		if ( $pageTitle === null ) {
			throw new InvalidInputException( 'Invalid article requested', 'invalid-title' );
		}

		if ( $pageTitle && $pageTitle->isExternal() ) {
			throw new CrossWikiException( 'Interwiki to ' . $pageTitle->getInterwiki() . ' not implemented ', 'default' );
		}

		if ( $pageTitle->getNamespace() === NS_TOPIC ) {
			$workflowId = UUID::create( strtolower( $pageTitle->getRootText() ) );
		}
		if ( $workflowId !== null ) {
			$workflow = $this->loadWorkflowById( $pageTitle, $workflowId );
		} else {
			$workflow = $this->loadWorkflow( $pageTitle );
		}

		return new WorkflowLoader(
			$workflow,
			$this->blockFactory,
			$this->submissionHandler
		);
	}

	/**
	 * @param Title $title
	 * @return Workflow
	 * @throws InvalidDataException
	 */
	protected function loadWorkflow( \Title $title ) {
		global $wgUser, $wgFlowDefaultWorkflow;
		$storage = $this->storage->getStorage( 'Workflow' );

		$found = $storage->find( array(
			'workflow_type' => $wgFlowDefaultWorkflow,
			'workflow_wiki' => $title->isLocal() ? wfWikiId() : $title->getTransWikiID(),
			'workflow_namespace' => $title->getNamespace(),
			'workflow_title_text' => $title->getDBkey(),
		) );
		if ( $found ) {
			$workflow = reset( $found );
		} else {
			$workflow = Workflow::create( $wgFlowDefaultWorkflow, $wgUser, $title );
		}

		return $workflow;
	}

	/**
	 * @param Title|false $title
	 * @param string $workflowId
	 * @return Workflow
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

		return $workflow;
	}
}
