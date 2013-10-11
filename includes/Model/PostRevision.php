<?php

namespace Flow\Model;

use User;
use MWTimestamp;

class PostRevision extends AbstractRevision {
	protected $postId;

	// denormalized data the must not change between revisions of same post
	protected $origCreateTime;
	protected $origUserId;
	protected $origUserText;
	protected $replyToId;

	// Data that is loaded externally and set
	protected $children;

	// Create a brand new root post for a brand new topic.  Creating replies to
	// an existing post(incl topic root) should use self::reply.
	// @param Workflow $topic
	// @param string $content The title of the topic(they are revisionable as well)
	static public function create( Workflow $topic, $content ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->postId = $topic->getId();
		$obj->origUserId = $obj->userId = $topic->getUserId();
		$obj->origUserText = $obj->userText = $topic->getUserText();
		$obj->origCreateTime = wfTimestampNow();
		$obj->replyToId = null; // not a reply to anything
		$obj->prevRevId = null; // no parent revision
		$obj->changeType = 'flow-rev-message-new-post';
		$obj->setContent( $content );
		return $obj;
	}

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $row['rev_id'] !== $row['tree_rev_id'] ) {
			throw new \MWException( 'tree revision doesn\'t match provided revision' );
		}
		$obj = parent::fromStorageRow( $row, $obj );

		$obj->replyToId = UUID::create( $row['tree_parent_id'] );
		$obj->postId = UUID::create( $row['tree_rev_descendant_id'] );
		$obj->origCreateTime = $row['tree_orig_create_time'];
		$obj->origUserId = $row['tree_orig_user_id'];
		$obj->origUserText = $row['tree_orig_user_text'];
		return $obj;
	}

	static public function toStorageRow( $rev ) {
		return parent::toStorageRow( $rev ) + array(
			'tree_parent_id' => $rev->replyToId ? $rev->replyToId->getBinary() : null,
			'tree_rev_descendant_id' => $rev->postId->getBinary(),
			'tree_rev_id' => $rev->revId->getBinary(),
			// rest of tree_ is denormalized data about first post revision
			'tree_orig_create_time' => $rev->origCreateTime,
			'tree_orig_user_id' => $rev->origUserId,
			'tree_orig_user_text' => $rev->origUserText,
		);
	}

	public function reply( User $user, $content, $changeType = 'flow-rev-message-reply' ) {
		$reply = new self;
		// No great reason to create two uuid's,  a post and its first revision can share a uuid
		$reply->revId = $reply->postId = UUID::create();
		$reply->userId = $reply->origUserId = $user->getId();
		$reply->userText = $reply->origUserText = $user->getName();
		$reply->origCreateTime = wfTimestampNow();
		$reply->setContent( $content );
		$reply->replyToId = $this->postId;
		$reply->changeType = $changeType;
		return $reply;
	}

	public function getPostId() {
		return $this->postId;
	}

	public function getCreatorId() {
		return $this->origUserId;
	}

	/**
	 * Returns the username or false if the current user has insufficient
	 * permissions to access this data for this post.
	 *
	 * @param null $user
	 * @return string|bool
	 */
	public function getCreatorName( $user = null ) {
		if ( $this->isAllowed( $user ) ) {
			$user = User::newFromId( $this->getCreatorId() );

			// @todo: not here
			if ( $user->isAnon() ) {
				return wfMessage( 'flow-user-anonymous' )->plain();
			}

			return $this->getCreatorNameRaw();
		} else {
			return false;
		}
	}

	public function getCreatorNameRaw() {
		return $this->origUserText;
	}

	public function isTopicTitle() {
		return $this->replyToId === null;
	}

	public function getReplyToId() {
		return $this->replyToId;
	}

	public function setChildren( array $children ) {
		$this->children = $children;
	}

	public function getChildren() {
		if ( $this->children === null ) {
			throw new \Exception( 'Children not loaded for post: ' . $this->postId->getHex() );
		}
		return $this->children;
	}

	public function getParticipants() {
		$children = $this->getChildren();
		$creators = array();

		foreach ( $children as $child ) {
			$name = $this->getCreatorName();
			if ( $name !== false ) {
				// origUserText is unique to user, creatorname may return multiple "Anonymous"
				$creators[$this->origUserText] = $name;
			}

			$creators += $child->getParticipants();
		}

		return array_values( $creators );
	}

	public function findDescendant( $postId ) {
		if ( ! $postId instanceof UUID ) {
			$postId = UUID::create( $postId );
		}

		$stack = array( $this );
		while( $stack ) {
			$post = array_pop( $stack );
			if ( $post->getPostId()->equals( $postId ) ) {
				return $post;
			}
			foreach ( $post->getChildren() as $child ) {
				$stack[] = $child;
			}
		}

		throw new \Exception( 'Requested postId is not available within post tree' );
	}

	/**
	 * Returns 1 if $this is newer than $rev, -1 is $rev is newer than
	 * $this, and 0 if created at same moment.
	 * TODO: better name.  This is if the POST is newer, not the revision.
	 */
	public function compareCreateTime( PostRevision $rev ) {
		return strcmp( $rev->postId->getNumber(), $this->postId->getNumber() );
	}

	public function getRevisionType() {
		return 'post';
	}

	// Posts are unformatted if they are title posts, formatted otherwise.
	public function isFormatted() {
		if ( !is_null( $this->replyToId ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function isAllowedToEdit( $user ) {
		if ( $user->isAnon() ) {
			return false;
		}
		return $user->getId() == $this->getCreatorId() || $user->isAllowed( 'flow-edit-post' );
	}
}


