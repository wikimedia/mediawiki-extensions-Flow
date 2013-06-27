<?php

namespace Flow\Model;

use User;
use UIDGenerator;

class Summary extends AbstractRevision {
	protected $workflowId;

	static public function create( Workflow $workflow, User $user, $content ) {
		$obj = new self;
		$obj->revId = UIDGenerator::newTimestampedUID128();
		$obj->workflowId = $workflow->getId();
		$obj->content = $content;
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = null; // no prior revision
		return $obj;
	}

	static public function fromStorageRow( array $row ) {
		if ( $row['rev_type'] !== 'summary' ) {
			throw new \MWException( 'Wrong revision type: ' . $row['rev_type'] );
		}
		$obj = parent::fromStorageRow( $row );
		$obj->workflowId = $row['summary_workflow_id'];
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'rev_type' => 'summary',
			'summary_rev_id' => $obj->revId,
			'summary_workflow_id' => $obj->workflowId,
		);
	}
}

