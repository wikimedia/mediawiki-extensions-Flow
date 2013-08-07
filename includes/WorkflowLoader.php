<?php

namespace Flow;

use Flow\Block\SummaryBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Model\Definition;
use Flow\Model\UUID;
use Flow\Model\Workflow;

class WorkflowLoader {

	protected $workflow, $definition;

	public function __construct( $pageTitle, UUID $workflowId = null, $definition = false ) {
		if ( $pageTitle === null ) {
			throw new \MWException( 'Invalid article requested' );
		}
		if ( $pageTitle->mInterwiki ) {
			throw new \MWException( 'Interwiki not implemented yet' );
		}
		if ( $pageTitle->getArticleID() === 0 ) {
			throw new \MWException( 'Can only load workflows for existing page' );
		}

		$this->container = include dirname(__FILE__). '/../container.php';
		$this->definitionRequest = $definition;

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

	protected function loadDefinition() {
		global $wgFlowDefaultWorkflow;

		$repo = $this->container['storage.definition'];
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
				throw new MWException( "Unknown flow type '$workflowName' requested" );
			}
		}
		return $definition;
	}

	public function createBlocks( ) {
		switch( $this->definition->getType() ) {
		case 'discussion':
			return array(
				'summary' => new SummaryBlock( $this->workflow, $this->container['storage'] ),
				'topics' => new TopicListBlock( $this->workflow, $this->container['storage'], $this->container['loader.root_post'] ),
			);

		case 'topic':
			return array(
				'topic' => new TopicBlock( $this->workflow, $this->container['storage'], $this->container['loader.root_post'] ),
			);

		default:
			throw new \MWException( 'Not Implemented' );
		}
	}
}