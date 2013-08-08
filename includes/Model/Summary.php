<?php

namespace Flow\Model;

use User;

class Summary extends AbstractRevision {
	protected $workflowId;

	static public function create( Workflow $workflow, User $user, $content ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->workflowId = $workflow->getId();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = null; // no prior revision
		$obj->decompressedContent = $obj->content = $content;
		$flags = \Revision::compressRevisionText( $obj->content );
		$obj->flags = explode( ',', $flags );
		return $obj;
	}

	static public function fromStorageRow( array $row ) {
		$obj = parent::fromStorageRow( $row );
		$obj->workflowId = UUID::create( $row['summary_workflow_id'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return parent::toStorageRow( $obj ) + array(
			'rev_type' => 'summary',
			'summary_rev_id' => $obj->revId->getBinary(),
			'summary_workflow_id' => $obj->workflowId->getBinary(),
		);
	}

	public function getRevisionType() {
		return 'summary';
	}
}

