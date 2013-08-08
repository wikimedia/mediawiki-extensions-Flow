<?php

namespace Flow\Model;

use User;

abstract class AbstractRevision {
	protected $revId;
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
	// Only populated when external store is in use
	protected $contentUrl;

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
		$obj->content = $row['rev_content'];
		if ( isset( $row['rev_content_url'] ) ) {
			// only exists when external store is being used
			$obj->contentUrl = $row['rev_content_url'];
		}
		$obj->flags = explode( ',', $row['rev_flags'] );
		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return array(
			'rev_id' => $obj->revId->getBinary(),
			'rev_user_id' => $obj->userId,
			'rev_user_text' => $obj->userText,
			'rev_parent_id' => $obj->prevRevision ? $obj->prevRevision->getBinary() : null,
			'rev_comment' => $obj->comment,
			'rev_type' => $obj->getRevisionType(),

			'rev_content' => $obj->content,
			'rev_content_url' => $obj->contentUrl,
			'rev_flags' => implode( ',', $obj->flags ),
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
			$obj->contentUrl = null;
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

