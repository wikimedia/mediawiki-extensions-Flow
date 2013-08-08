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
	// TODO: i18n key may be too limiting, consider allowing custom revision comments
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
		$obj->prevRevision = UUID::create( $row['rev_parent_id'] );
		$obj->comment = $row['rev_comment'];

		$obj->textId = $row['rev_text_id'];
		$obj->content = $row['text_content'];
		$obj->flags = explode( ',', $row['text_flags'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return array(
			'rev_id' => $obj->revId->getBinary(),
			'rev_user_id' => $obj->userId,
			'rev_user_text' => $obj->userText,
			'rev_parent_id' => $obj->prevRevision ? $obj->prevRevision->getBinary() : null,
			'rev_comment' => $obj->comment,
			'rev_text_id' => $obj->textId,
			'rev_type' => $obj->getRevisionType(),

			'text_content' => $obj->content,
			'text_flags' => implode( ',', $obj->flags ),
		);
	}

	/**
	 * NOTE: No guarantee is made here regarding if $this is the newest revision.  Validation
	 * must happen externally.  DB *will* throw an exception if this attempts to write to db
	 * and it is not the most recent revision.
	 */
	public function newNullRevision( User $user ) {
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

