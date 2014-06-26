<?php

namespace Flow;

use Flow\Model\Definition;
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
	 * @param string|false $definitionRequest
	 * @return WorkflowLoader
	 * @throws InvalidInputException
	 * @throws CrossWikiException
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
	 * @param Title|false $title
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
	 * @param string $id
	 * @return Definition
	 * @throws InvalidInputException
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
