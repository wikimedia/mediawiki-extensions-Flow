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

	/**
	 * Variables callback functions & their results will be saved to.
	 *
	 * @var array
	 */
	protected $recursiveCallbacks = array();
	protected $recursiveResults = array();

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
	 * Runs all registered callback on every descendant of this post.
	 *
	 * Used to defer going recursive more than once: if all recursive
	 * functionality is first registered, we can fetch all results in one go.
	 *
	 * @param callable $callback The callback to call. 2 parameters:
	 * PostRevision (the post being iterated) & $result (the current result at
	 * time of iteration). They must respond with [ $result, $continue ],
	 * where $result is the result after that post's iteration & $continue a
	 * boolean value indicating if the iteration still needs to continue
	 * @param mixed $init The initial $result value to be fed to the callback
	 * @return int $i Identifier to pass to getRecursiveResult() to retrieve
	 * the callback's result
	 */
	public function registerRecursive( $callback, $init ) {
		$i = count( $this->recursiveResults );

		$this->recursiveCallbacks[$i] = $callback;
		$this->recursiveResults[$i] = $init;

		return $i;
	}

	/**
	 * Returns the result of a specific callback, after having iterated over
	 * all children.
	 *
	 * @param int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerRecursive()
	 * @return mixed
	 */
	public function getRecursiveResult( $registered ) {
		$this->recursiveResults = $this->descendRecursive(
			$this->recursiveCallbacks,
			$this->recursiveResults
		);

		// Once all callbacks have run, null the callbacks to make sure they won't run again
		$this->recursiveCallbacks = array_fill( 0, count( $this->recursiveResults ), null );

		return $this->recursiveResults[$registered];
	}

	/**
	 * Runs all registered callback on every descendant of this post & recursive
	 * from there on, until $maxDepth has been reached.
	 *
	 * @param array $callbacks Array of callbacks to execute. Callbacks are fed
	 * 2 parameters: PostRevision (the post being iterated) & $result (the current
	 * result at time of iteration). They must respond with [ $result, $continue ],
	 * where $result is the result after that post's iteration & $continue a
	 * boolean value indicating if the iteration still needs to continue
	 * @param array $results Array of (initial or temporary) results per callback
	 * @param int[optional] $maxDepth The maximum depth to travel
	 * @return array $results All final results of the callbacks
	 */
	protected function descendRecursive( array $callbacks, array $results, $maxDepth = 10 ) {
		if ( $maxDepth <= 0 ) {
			return;
		}

		foreach ( $this->getChildren() as $child ) {
			$continue = false;

			foreach ( $callbacks as $i => $callback ) {
				if ( is_callable( $callback ) ) {
					$return = $callback( $child, $results[$i] );

					// Callbacks respond with: [ result, continue ]
					// Continue can be set to false if a callback has completed
					// what it set out to do, then we can stop running it.
					$results[$i] = $return[0];
					$continue |= $return[1];

					// If this specific callback has responded it should no longer
					// continue, get rid of it.
					if ( $return[1] === false ) {
						$callbacks[$i] = null;
					}
				}
			}

			// All of the callbacks have completed what they set out to do = quit
			if ( !$continue ) {
				break;
			}

			$results = $child->descendRecursive( $callbacks, $results, $maxDepth - 1 );
		}

		return $results;
	}

	/**
	 * Registers callback function to calculate the total number of descendants.
	 *
	 * @return int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerRecursive()
	 */
	public function registerDescendantCount() {
		/**
		 * Adds 1 to the total value per post that's iterated over.
		 *
		 * @param PostRevision $post
		 * @param int $result
		 * @return array Return array in the format of [result, continue]
		 */
		$callback = function( PostRevision $post, $result ) {
			return array( $result + 1, true );
		};

		return $this->registerRecursive( $callback, 0 );
	}

	/**
	 * Registers callback function to compile a list of participants.
	 *
	 * @param string[optional] $anonymousBehaviour string Behaviour to use for anonymous users. Options:
	 * once: Include all anonymous users as one combined user.
	 * each: Include each anonymous user separately.
	 * none: Do not include anonymous users
	 * @return int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerRecursive()
	 */
	public function registerParticipants( $anonymousBehaviour = 'each' ) {
		/**
		 * Adds the user object of this post's creator.
		 *
		 * @param PostRevision $post
		 * @param int $result
		 * @return array Return array in the format of [result, continue]
		 */
		$callback = function( $post, $result ) use ( $anonymousBehaviour ) {
			$creator = $post->getCreator();

			if ( $creator instanceof User ) {
				if ( $creator->isAnon() ) {
					if ( $anonymousBehaviour === 'once' ) {
						$result['anon'] = $creator;
					} elseif ( $anonymousBehaviour === 'each' ) {
						$result[$creator->getName()] = $creator;
					} elseif ( $anonymousBehaviour === 'none' ) {
						// Do nothing
					} else {
						throw new MWException( "Unknown anonymous behaviour $anonymousBehaviour" );
					}
				} else {
					$result[$creator->getId()] = $creator;
				}
			}

			return array( $result, true );
		};

		return $this->registerRecursive( $callback, array() );
	}

	/**
	 * Registers callback function to find a specific post within a post's children.
	 *
	 * @param UUID $postId The id of the post to find.
	 * @return int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerRecursive()
	 */
	public function registerDescendant( $postId ) {
		if ( !$postId instanceof UUID ) {
			$postId = UUID::create( $postId );
		}

		/**
		 * Rrturns the found post.
		 *
		 * @param PostRevision $post
		 * @param int $result
		 * @return array Return array in the format of [result, continue]
		 */
		$callback = function( $post, $result ) use ( &$postId ) {
			if ( $post->getPostId()->equals( $postId ) ) {
				return array( $post, false );
			}
		};

		return $this->registerRecursive( $callback, false );
	}

	/**
	 * Get the number of descendant posts.
	 *
	 * @param int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerDescendantCount()
	 * @return int
	 */
	public function getDescendantCount( $registered ) {
		return $this->getRecursiveResult( $registered );
	}

	/**
	 * Get a list of all participants on this level.
	 *
	 * @param int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerParticipants()
	 * @return array
	 */
	public function getParticipants( $registered ) {
		return $this->getRecursiveResult( $registered );
	}

	/**
	 * Find a specific post withing the post's children.
	 *
	 * @param int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerDescendant()
	 * @return PostRevision
	 * @throws \MWException
	 */
	public function getDescendant( $registered ) {
		$foundPost = $this->getRecursiveResult( $registered );

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


