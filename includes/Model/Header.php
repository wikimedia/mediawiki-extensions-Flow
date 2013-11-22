<?php

namespace Flow\Model;

use User;

class Header extends AbstractRevision {
	protected $workflowId;

	static public function create( Workflow $workflow, User $user, $content, $changeType = 'flow-create-header' ) {
		$obj = new self;
		$obj->workflow = $workflow;
		$obj->revId = UUID::create();
		$obj->workflowId = $workflow->getId();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = null; // no prior revision
		$obj->setContent( $content );
		$obj->changeType = $changeType;
		return $obj;
	}

	static public function fromStorageRow( array $row, $obj = null ) {
		$obj = parent::fromStorageRow( $row, $obj );
		$obj->workflowId = UUID::create( $row['header_workflow_id'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'rev_type' => 'header',
			'header_rev_id' => $obj->revId->getBinary(),
			'header_workflow_id' => $obj->workflowId->getBinary(),
		);
	}

	public function getRevisionType() {
		return 'header';
	}

	public function getWorkflowId() {
		return $this->workflowId;
	}

	public function getWorkflow() {
		if ( $this->workflow === null ) {
			throw new \MWException( 'Workflow not loaded for header: ' . $this->revId->getHex() );
		}
		return $this->workflow;
	}

	public function setWorkflow( Workflow $workflow ) {
		$this->workflow = $workflow;
	}

	/**
	 * Headers are more like wiki pages and as such are moderated
	 * one revision at a time.
	 */
	public function needsModerateHistorical( $state ) {
		return false;
	}
}
