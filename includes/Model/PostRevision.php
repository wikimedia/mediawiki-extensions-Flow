<?php

namespace Flow\Model;

use User;
use MWTimestamp;

class PostRevision extends AbstractRevision {
	const MAX_TOPIC_LENGTH = 260;

	protected $postId;

	// denormalized data that must not change between revisions of same post
	protected $origCreateTime;
	protected $origUserId;
	protected $origUserText;
	protected $replyToId;

	// Data that is loaded externally and set
	protected $children;
	protected $depth;

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
		$obj->changeType = 'new-post';
		$obj->setContent( $content );
		// A newly created post has no children and a depth of 0
		$obj->setChildren( array() );
		$obj->setDepth( 0 );

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

	public function reply( User $user, $content, $changeType = 'reply' ) {
		$reply = new self;
		// No great reason to create two uuid's,  a post and its first revision can share a uuid
		$reply->revId = $reply->postId = UUID::create();
		$reply->userId = $reply->origUserId = $user->getId();
		$reply->userText = $reply->origUserText = $user->getName();
		$reply->origCreateTime = wfTimestampNow();
		$reply->replyToId = $this->postId;
		$reply->setContent( $content );
		$reply->changeType = $changeType;
		$reply->setChildren( array() );
		$reply->setDepth( $this->getDepth() + 1 );

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

		// Not using $creator->getId() to avoid having to fetch the id if that
		// User object was created via newFromName
		return $creator === false ? false : $this->getCreatorIdRaw();
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

		// Not using $creator->getName() to avoid having to fetch the name if
		// that User object was created via newFromId
		return $creator === false ? false : $this->getCreatorNameRaw();
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
			if ( $this->getCreatorIdRaw() != 0 ) {
				$user = User::newFromId( $this->getCreatorIdRaw() );
			} else {
				// Don't validate username; (anon) IP user object is fine.
				$user = User::newFromName( $this->getCreatorNameRaw(), false );
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

	public function setDepth( $depth ) {
		$this->depth = $depth;
	}

	public function getDepth() {
		if ( $this->depth === null ) {
			throw new \MWException( 'Depth not loaded for post: ' . $this->postId->getHex() );
		}
		return $this->depth;
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
	 * @param string[optional] $label Can be used to make the identifier
	 * slightly more descriptive (just simple integers can be quite opaque when
	 * debugging)
	 * @return int Identifier to pass to getRecursiveResult() to retrieve
	 * the callback's result
	 */
	public function registerRecursive( $callback, $init, $label = '' ) {
		$i = count( $this->recursiveResults );
		$identifier = "$i-$label";

		$this->recursiveCallbacks[$identifier] = $callback;
		$this->recursiveResults[$identifier] = $init;

		return $identifier;
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
		list( $callbacks, $results ) = $this->descendRecursive(
			$this->recursiveCallbacks,
			$this->recursiveResults
		);
		$this->recursiveResults = $results;

		// Once all callbacks have run, null the callbacks to make sure they won't run again
		$this->recursiveCallbacks = array_fill( 0, count( $this->recursiveResults ), null );

		return $this->recursiveResults[$registered];
	}

	/**
	 * Runs all registered callback on this post and all descendants to a
	 * maximum depth of $maxDepth
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

		$continue = false;
		foreach ( $callbacks as $i => $callback ) {
			if ( is_callable( $callback ) ) {
				$return = call_user_func( $callback, $this, $results[$i] );

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
		if ( $continue ) {
			foreach ( $this->getChildren() as $child ) {
				// Also fetch callbacks from children, some may have been nulled to
				// prevent further execution.
				list( $callbacks, $results ) = $child->descendRecursive( $callbacks, $results, $maxDepth - 1 );

				// Check to see if we should exit
				if ( ! count( array_filter( $callbacks ) ) ) {
					break;
				}
			}
		}

		return array( $callbacks, $results );
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

		// Start at -1 because parent doesn't count as "descendant"
		return $this->registerRecursive( $callback, -1, 'count' );
	}

	/**
	 * Registers callback function to compile a list of participants.
	 *
	 * @return int $registered The identifier that was returned when registering
	 * the callback via PostRevision::registerRecursive()
	 */
	public function registerParticipants() {
		/**
		 * Adds the user object of this post's creator.
		 *
		 * @param PostRevision $post
		 * @param int $result
		 * @return array Return array in the format of [result, continue]
		 */
		$callback = function( PostRevision $post, $result ) {
			$creator = $post->getCreator();
			if ( $creator instanceof User ) {
				$result[$post->getCreatorName()] = $creator;
			}

			return array( $result, true );
		};

		return $this->registerRecursive( $callback, array(), 'participants' );
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
		 * Returns the found post.
		 *
		 * @param PostRevision $post
		 * @param int $result
		 * @return array Return array in the format of [result, continue]
		 */
		$callback = function( PostRevision $post, $result ) use ( &$postId ) {
			if ( $post->getPostId()->equals( $postId ) ) {
				return array( $post, false );
			}
			return array( false, true );
		};

		return $this->registerRecursive( $callback, false, 'descendant-' . $postId->getHex() );
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

	public function isCreator( $user ) {
		if ( $user->isAnon() ) {
			return false;
		}
		return $user->getId() == $this->getCreatorId();
	}

	/**
	 * @param string $name User name
	 */
	public function setOrigUserText( $name ) {
		$this->origUserText = $name;
	}
}
