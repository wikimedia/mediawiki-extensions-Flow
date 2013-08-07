<?php

namespace Flow\Model;

use User;

abstract class AbstractRevision {
	protected $revId;
	protected $textId;
	protected $userId;
	protected $userText;
	protected $flags = array();
	// An i18n message key indicating what kind of change this revision is
	// primary use case is the a revision history list.
	protected $comment;
	protected $prevRevision;

	// content
	protected $contentModel;
	protected $contentFormat;
	protected $content;

	static public function fromStorageRow( array $row ) {
		$obj = new static;
		if ( $row['rev_type'] !== $obj->getRevisionType() ) {
			throw new \MWException( sprintf(
				"Wrong revision type, expected '%s' but received '%s'",
				$obj->getRevisionType(),
				$row['rev_type']
			) );
		}
		$obj->revId = UUID::create( $row['rev_id'] );
		$obj->userId = $row['rev_user_id'];
		$obj->userText = $row['rev_user_text'];
		$obj->flags = explode( ',', $row['rev_flags'] );
		if ( $row['rev_parent_id'] ) {
			$obj->prevRevision = UUID::create( $row['rev_parent_id'] );
		} else {
			$obj->prevRevision = null;
		}
		$obj->comment = $row['rev_comment'];

		$obj->textId = $row['rev_text_id'];
		$obj->content = $row['text_content'];
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		$prevRevision = null;
		if ( $obj->prevRevision ) {
			$prevRevision = $obj->prevRevision->getBinary();
		}
		return array(
			'rev_id' => $obj->revId->getBinary(),
			'rev_user_id' => $obj->userId,
			'rev_user_text' => $obj->userText,
			'rev_flags' => implode( ',', $obj->flags ),
			'rev_parent_id' => $prevRevision,
			'rev_comment' => $obj->comment,
			'rev_text_id' => $obj->textId,
			'rev_type' => $obj->getRevisionType(),

			'text_content' => $obj->content,
		);
	}

	public function newNullRevision( User $user ) {
		// TODO: how do we know this is the latest revision? we dont ...
		// basically, this is very very wrong :-(
		$obj = clone $this;
		$obj->revId = UUID::create();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = $this->revId;
		return $obj;
	}

	public function newNextRevision( User $user, $content ) {
		$obj = $this->newNullRevision( $user );
		$obj->flags = array();
		if ( $content !== $obj->content ) {
			$obj->content = $content;
			$obj->textId = null;
		}
		return $obj;
	}

	public function getRevisionId() {
		return $this->revId;
	}

	public function getContent() {
		if ( $this->content === null ) {
			throw new \MWException( 'Content not loaded' );
		}
		return $this->content;
	}

	public function getTextId() {
		return $this->textId; // not available on creation
	}

	public function getPrevRevisionId() {
		return $this->prevRevision;
	}

	public function getComment() {
		return $this->comment;
	}

	public function addFlag( User $user, $flag, $comment ) {
		if ( $this->isFlagged( $flag ) ) {
			// already flagged
			return $this;
		}
		if ( false !== strpos( ',', $flag ) ) {
			throw new \MWException( 'Invalid flag name: contains comma' );
		}
		$updated = $this->newNullRevision( $user );
		$updated->flags[] = $flag;
		$updated->comment = $comment;
		return $updated;
	}

	public function removeFlag( User $user, $flag, $comment ) {
		if ( !$this->isFlagged( $flag ) ) {
			return $this;
		}
		$updated = $this->newNullRevision( $user );
		unset( $updated->flags[array_search( $flag, $updated->flags )] );
		$updated->comment = $comment;
		return $updated;
	}

	public function isFlagged( $flag ) {
		return false !== array_search( $flag, $this->flags );
	}
}

