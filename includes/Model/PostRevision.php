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

	/**
	 * Get the user ID of the user who created this post.
	 * Checks permissions and returns false 
	 *
	 * @param $user User The user to check permissions for.
	 * @return int|bool The user ID, or false
	 */
	public function getCreatorId( $user = null ) {
		$creator = $this->getCreator( $user );

		return $creator === false ? false : $creator->getId();
	}

	/**
	 * Get the username of the User who created this post.
	 * Checks permissions, and returns false if the current user is not permitted
	 * to access that information
	 *
	 * @param User $user The user to check permissions for.
	 * @return string|bool The username of the User who created this post.
	 */
	public function getCreatorName( $user = null ) {
		$creator = $this->getCreator( $user );

		return $creator === false ? false : $creator->getName();
	}

	/**
	 * Get the User who created this post.
	 * Checks permissions, and returns false if the current user is not permitted
	 * to access that information
	 *
	 * @param User $user The user to check permissions for.
	 * @return User|bool The username of the User who created this post.
	 */
	public function getCreator( $user = null ) {
		if ( $this->isAllowed( $user ) ) {
			if ( $this->getCreatorIdRaw() === 0 ) {
				$user = User::newFromId( $this->getCreatorIdRaw() );
			} else {
				$user = User::newFromName( $this->getCreatorNameRaw() );
			}

			return $user;
		} else {
			return false;
		}
	}

	public function getCreatorNameRaw() {
		return $this->origUserText;
	}

	public function getCreatorIdRaw() {
		return $this->origUserId;
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
			throw new \MWException( 'Children not loaded for post: ' . $this->postId->getHex() );
		}
		return $this->children;
	}

	/**
	 * Get the amount of posts in this topic.
	 *
	 * @return int
	 */
	public function getChildCount() {
		return count( $this->getChildren() );
	}

	/**
	 * Runs a callback on every descendant of this post.
	 * @param  callable $callback The callback to call. Accepts two parameters:
	 * $child: The child PostRevision.
	 * $cancelCallback: A callback that can be called to abort execution.
	 * @param  int      $maxDepth The maximum depth to travel
	 * @return bool     Whether or not execution was cancelled
	 */
	public function foreachDescendant( $callback, $maxDepth = 10 ) {
		$cancel = false;

		$cancelCallback = function() use ( &$cancel ) {
			$cancel = true;
		};

		foreach( $this->getChildren() as $child ) {
			call_user_func( $callback, $child, $cancelCallback );

			if ( $cancel ) {
				return true;
			}

			$cancel = $child->foreachDescendant( $callback, $maxDepth - 1 );
		}

		return false;
	}

	/**
	 * Get the number of descendant posts.
	 *
	 * @return int
	 */
	public function getDescendantCount() {
		$count = $this->getChildCount();

		$this->foreachDescendant( function( $post ) use ($count) {
			$count++;
		} );

		return $count;
	}

	/**
	 * Get a list of all participants on this level.
	 *
	 * @param $anonymousBehaviour string Behaviour to use for anonymous users. Options:
	 * once: Include all anonymous users as one combined user.
	 * each: Include each anonymous user separately.
	 * none: Do not include anonymous users
	 * @return array
	 */
	public function getParticipants( $anonymousBehaviour = 'each' ) {
		$creators = array();

		$this->foreachDescendant( function( $post ) use ( &$creators, $anonymousBehaviour ) {
			$creator = $post->getCreator();

			if ( ! $creator instanceof User ) {
				return;
			}

			if ( $creator->isAnon() ) {
				if ( $anonymousBehaviour === 'once' ) {
					$creators['anon'] = $creator;
				} elseif ( $anonymousBehaviour === 'each' ) {
					$creators[$creator->getName()] = $creator;
				} elseif ( $anonymousBehaviour === 'none' ) {
					// Do nothing
				} else {
					throw new MWException( "Unknown anonymous behaviour $anonymousBehaviour" );
				}
			} else {
				$creators[$creator->getId()] = $creator;
			}
		} );

		return $creators;
	}

	public function findDescendant( $postId ) {
		if ( ! $postId instanceof UUID ) {
			$postId = UUID::create( $postId );
		}

		$foundPost = false;

		$this->foreachDescendant( function( $post, $cancelCallback ) use ( &$postId ) {
			if ( $post->getPostId()->equals( $postId ) ) {
				$foundPost = $post;
				$cancelCallback();
			}
		} );

		if ( $foundPost !== false ) {
			return $foundPost;
		} else {
			throw new \MWException( 'Requested postId is not available within post tree' );
		}
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


