<?php

namespace Flow\Model;

use User;
use UIDGenerator;

class Summary {
	protected $revId;
	protected $workflowId;
	protected $text;
	protected $userId;
	protected $userText;
	protected $deleted;
	protected $prevRevision;

	static public function create( Workflow $workflow, User $user, $content ) {
		$obj = new self;
		$obj->revId = UIDGenerator::newTimestampedUID128();
		$obj->workflowId = $workflow->getId();
		$obj->text = $content;
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->deleted = 0;
		$obj->prevRevision = null; // no prior revision
		return $obj;
	}

	static public function loadFromRow( array $row ) {
		if ( $row['rev_type'] !== 'summary' ) {
			throw new \MWException( 'Wrong revision type: ' . $row['rev_type'] );
		}
		$obj = new self;
		$obj->revId = $row['rev_id'];
		$obj->workflowId = $row['summary_workflow_id'];
		$obj->text = $row['rev_text'];
		$obj->userId = $row['rev_user_id'];
		$obj->userText = $row['rev_user_text'];
		$obj->deleted = $row['rev_deleted'];
		$obj->prevRevision = $row['rev_parent_id'];
		return $obj;
	}

	static public function toStorageRow( Summary $obj ) {
		return array(
			'summary_rev_id' => $obj->revId,
			'summary_workflow_id' => $obj->workflowId,

			'rev_id' => $obj->revId,
			'rev_type' => 'summary',
			'rev_text' => $obj->text,
			'rev_user_id' => $obj->userId,
			'rev_user_text' => $obj->userText,
			'rev_deleted' => $obj->deleted,
			'rev_parent_id' => $obj->prevRevision,
		);
	}

	public function newNullRevision( User $user ) {
		// TODO: how do we know this is the latest revision? we dont ...
		// basically, this architecture is very very wrong :-(
		$obj = clone $this;
		$obj->revId = UIDGenerator::newTimestampedUID128();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = $this->revId;
		return $obj;
	}

	public function newNextRevision( User $user, $content ) {
		$obj = $this->newNullRevision( $user );
		$obj->deleted = 0;
		$obj->text = $content;
		return $obj;
	}

	public function getRevisionId() {
		return $this->revId;
	}

	public function getContent() {
		return $this->text;
	}

	public function getPrevRevisionId() {
		return $this->prevRevision;
	}
}

