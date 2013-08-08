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
	protected $content;
	// Only populated when external store is in use
	protected $contentUrl;
	// This is decompressed on-demand from $this->content in self::getContent()
	protected $decompressedContent;

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new static;
		} elseif ( !$obj instanceof static ) {
			throw new \Exception( 'wrong object type' );
		}
		$obj->revId = UUID::create( $row['rev_id'] );
		$obj->userId = $row['rev_user_id'];
		$obj->userText = $row['rev_user_text'];
		$obj->prevRevision = UUID::create( $row['rev_parent_id'] );
		$obj->comment = $row['rev_comment'];
		// null if external store is not being used
		$obj->flags = explode( ',', $row['rev_flags'] );
		$obj->content = $row['rev_content'];
		$obj->contentUrl = $row['rev_content_url'];
		$obj->decompressedContent = null;
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
		$this->setContent( $content );
		return $obj;
	}

	public function getRevisionId() {
		return $this->revId;
	}

	public function getContent() {
		if ( $this->decompressedContent === null ) {
			$this->decompressedContent = \Revision::decompressRevisionText( $this->content, $this->flags );
		}
		return $this->decompressedContent;
	}

	protected function setContent( $content ) {
		if ( $content !== $this->getContent() ) {
			$this->content = $this->decompressedContent = $content;
			$this->contentUrl = null;
			// should this only remove a subset of flags?
			$this->flags = array();
		}
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

