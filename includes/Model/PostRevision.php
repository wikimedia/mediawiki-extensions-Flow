<?php

namespace Flow\Model;

use UIDGenerator;
use User;

class PostRevision {
		protected $postId;
		protected $revId;
		protected $text;
		protected $userId;
		protected $userText;
		protected $deleted = 0;
		protected $prevRevId;

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
				$obj->revId = UIDGenerator::newTimestampedUID128();
				$obj->postId = $topic->getId();
				$obj->text = $content;
				$obj->origUserId = $obj->userId = $topic->getUserId();
				$obj->origUserText = $obj->userText = $topic->getUserText();
				$obj->origCreateTime = wfTimestampNow();
				$obj->deleted = 0;
				$obj->replyToId = null; // not a reply to anything
				$obj->prevRevId = null; // no parent revision
				return $obj;
		}

		static public function loadFromRow( $row ) {
				if ( $row['rev_type'] !== 'post' ) {
					throw new \MWException( "Wrong revision type, expected 'post' but got : " . $row['rev_type'] );
				}
				if ( $row['rev_id'] !== $row['tree_rev_id'] ) {
					throw new \MWException( 'tree revision doesn\'t match provided revision' );
				}

				$obj = new self;
				$obj->replyToId = $row['tree_parent_id'];
				$obj->postId = $row['tree_rev_descendant'];
				$obj->origCreateTime = $row['tree_orig_create_time'];
				$obj->origUserId = $row['tree_orig_user_id'];
				$obj->origUserText = $row['tree_orig_user_text'];

				$obj->revId = $row['rev_id'];
				$obj->text = $row['rev_text'];
				$obj->userId = $row['rev_user_id'];
				$obj->userText = $row['rev_user_text'];
				$obj->deleted = $row['rev_deleted'];
				$obj->prevRevId = $row['rev_parent_id'];

				return $obj;
		}

		static public function toStorageRow( PostRevision $rev ) {
				return array(
						'tree_parent_id' => $rev->replyToId,
						'tree_rev_descendant' => $rev->postId,
						'tree_rev_id' => $rev->revId,
						// rest of tree_ is denormalized data about first post revision
						'tree_orig_create_time' => $rev->origCreateTime,
						'tree_orig_user_id' => $rev->origUserId,
						'tree_orig_user_text' => $rev->origUserText,

						'rev_id' => $rev->revId,
						'rev_type' => 'post',
						'rev_text' => $rev->text,
						'rev_user_id' => $rev->userId,
						'rev_user_text' => $rev->userText,
						'rev_deleted' => $rev->deleted,
						// This is the revision prior to the current
						'rev_parent_id' => $rev->prevRevId,
				);
		}

		public function reply( User $user, $content ) {
				$reply = new self;
				// No great reason to create two uuid's,  a post and its first revision can share a uuid
				$reply->revId = $reply->postId = UIDGenerator::newTimestampedUID128();
				$reply->userId = $reply->origUserId = $user->getId();
				$reply->userText = $reply->origUserText = $user->getName();
				$reply->origCreateTime = wfTimestampNow();
				$reply->text = $content;
				$reply->replyToId = $this->postId;
				return $reply;
		}

		public function newNullRevision( User $user, $content ) {
				$next = clone $this;
				$next->id = UIDGenerator::newTimestampedUID128();
				$next->replyToId = $this->revId;
				$next->userId = $user->getId();
				$next->userText = $user->getName();
				return $next;
		}

		public function newNextRevision( User $user, $content ) {
				$next = self::newNullRevision();
				$next->text = $content;
				return $next;
		}

		public function oversight( User $user, $something ) {
				// perform oversighting of existing revision
		}

		public function getPostId() { return $this->postId; }
		public function getContent() { return $this->text; }
		public function getUserText() { return $this->userText; }
		public function isTopicTitle() { return $this->replyToId === null; }
		public function getReplyToId() { return $this->replyToId; }

		public function setChildren( array $children ) {
			$this->children = $children;
		}
		public function getChildren() {
			if ( $this->children === null ) {
				throw new \Exception( 'Children not loaded' );
			}
			return $this->children;
		}
}


