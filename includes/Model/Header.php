<?php

namespace Flow\Model;

use User;

class Header extends AbstractRevision {
	protected $workflowId;

	static public function create( Workflow $workflow, User $user, $content, $changeType = 'flow-create-header' ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->workflowId = $workflow->getId();
		$obj->userId = $user->getId();
		if ( !$user->getId() ) {
			$obj->userIp = $user->getName();
		}
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

	/**
	 * @return UUID
	 */
	public function getCollectionId() {
		return $this->getWorkflowId();
	}

	/**
	 * @return HeaderCollection
	 */
	public function getCollection() {
		return HeaderCollection::newFromRevision( $this );
	}

	public function getObjectId() {
		return $this->getWorkflowId();
	}
}
