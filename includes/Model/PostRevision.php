<?php

namespace Flow\Model;

use User;
use Flow\Exception\DataModelException;
use Flow\Collection\PostCollection;

class PostRevision extends AbstractRevision {
	const MAX_TOPIC_LENGTH = 260;

	/**
	 * @var UUID
	 */
	protected $postId;

	// The rest of the properties are denormalized data that
	// must not change between revisions of same post

	/**
	 * @var integer
	 */
	protected $origUserId;

	/**
	 * @var string|null
	 */
	protected $origUserIp;

	/**
	 * @var string
	 */
	protected $origUserWiki;

	/**
	 * @var UUID|null
	 */
	protected $replyToId;

	/**
	 * @var PostRevision[]|null Optionally loaded list of children for this post.
	 */
	protected $children;

	/**
	 * @var int|null Optionally loaded distance of this post from the
	 *   root of this post tree.
	 */
	protected $depth;

	/**
	 * @var PostRevision|null Optionally loaded root of this posts tree.
	 *   This is always a topic title.
	 */
	protected $rootPost;

	/**
	 * Variables to which callback functions and their results will be saved.
	 *
	 * We have some functionality to defer recursive processing through the post
	 * tree up until the moment we actually need to. This makes it possible to
	 * register multiple callback functions that need to be run recursively, and
	 * execute them all one once, so we only have to go recursive once.
	 *
	 * Callbacks & initial result value will be saved when calling
	 * $this->registerRecursive(), final result will be saved after calling
	 * $this->descendRecursive().
	 * $this->getRecursiveResult() will return the result in $recursiveResults.
	 *
	 * @see PostRevision::registerRecursive()
	 * @see PostRevision::getRecursiveResult()
	 * @see PostRevision::descendRecursive()
	 *
	 * @var array
	 */
	protected $recursiveCallbacks = array();

	/**
	 * @see PostRevision::$recursiveCallbacks
	 *
	 * @var array
	 */
	protected $recursiveResults = array();

	/**
	 * Create a brand new root post for a brand new topic.  Creating replies to
	 * an existing post(incl topic root) should use self::reply.
	 *
	 * @param Workflow $topic
	 * @param string $content The title of the topic(they are Collection as well)
	 * @return PostRevision
	 */
	static public function create( Workflow $topic, $content ) {
		if ( $topic->getUserId() ) {
			$user = User::newFromId( $topic->getUserId() );
		} else {
			$user = User::newFromName( $topic->getUserIp(), false );
		}

		$obj = static::newFromId( $topic->getId(), $user, $content );

		$obj->changeType = 'new-post';
		// A newly created post has no children, a depth of 0, and
		// is the root of its tree.
		$obj->setChildren( array() );
		$obj->setDepth( 0 );
		$obj->rootPost = $obj;

		return $obj;
	}

	/**
	 * DO NOT USE THIS METHOD!
	 *
	 * Seriously, you probably don't want to use this method. Although it's kind
	 * of similar to Title::newFrom* or User::newFrom*, chances are slim to none
	 * that this will do what you'd expect.
	 * Unlike Title & User etc, a post is not something some object that can be
	 * used in isolation: a post should always be retrieved via it's parents,
	 * via a workflow, ...
	 * The only reason we have this method is so that, when failing to load a
	 * post, we can create a stub object.
	 *
	 * @param UUID $uuid
	 * @param User $user
	 * @param string $content
	 * @return PostRevision
	 */
	static public function newFromId( UUID $uuid, User $user, $content ) {
		$obj = new self;
		$obj->revId = UUID::create();
		$obj->postId = $uuid;

		list( $userId, $userIp, $userWiki ) = self::userFields( $user );
		$obj->origUserId = $obj->userId = $userId;
		$obj->origUserIp = $obj->userIp = $userIp;
		$obj->origUserWiki = $obj->userWiki = $userWiki;

		$obj->setReplyToId( null ); // not a reply to anything
		$obj->prevRevision = null; // no parent revision
		$obj->setContent( $content );

		return $obj;
	}

	/**
	 * @var string[] $row
	 * @var PostRevision|null $obj
	 * @return PostRevision
	 * @throws DataModelException
	 */
	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $row['rev_id'] !== $row['tree_rev_id'] ) {
			throw new DataModelException( 'tree revision doesn\'t match provided revision', 'process-data' );
		}
		/** @var $obj PostRevision */
		$obj = parent::fromStorageRow( $row, $obj );

		$obj->replyToId = UUID::create( $row['tree_parent_id'] );
		$obj->postId = UUID::create( $row['rev_type_id'] );
		$obj->origUserId = $row['tree_orig_user_id'];
		if ( isset( $row['tree_orig_user_ip'] ) ) {
			$obj->origUserIp = $row['tree_orig_user_ip'];
		// BC for tree_orig_user_text field
		} elseif ( isset( $row['tree_orig_user_text'] ) && $obj->origUserId === 0 ) {
			$obj->origUserIp = $row['tree_orig_user_text'];
		}
		$obj->origUserWiki = isset( $row['tree_orig_user_wiki'] ) ? $row['tree_orig_user_wiki'] : '';
		return $obj;
	}

	/**
	 * @param PostRevision $rev
	 * @return string[]
	 */
	static public function toStorageRow( $rev ) {
		return parent::toStorageRow( $rev ) + array(
			'tree_parent_id' => $rev->replyToId ? $rev->replyToId->getAlphadecimal() : null,
			'tree_rev_descendant_id' => $rev->postId->getAlphadecimal(),
			'tree_rev_id' => $rev->revId->getAlphadecimal(),
			// rest of tree_ is denormalized data about first post revision
			'tree_orig_user_id' => $rev->origUserId,
			'tree_orig_user_ip' => $rev->origUserIp,
			'tree_orig_user_wiki' => $rev->origUserWiki,
		);
	}

	/**
	 * @param Workflow $workflow
	 * @param User $user
	 * @param string $content
	 * @param string[optional] $changeType
	 * @return PostRevision
	 */
	public function reply( Workflow $workflow, User $user, $content, $changeType = 'reply' ) {
		$reply = new self;
		// No great reason to create two uuid's,  a post and its first revision can share a uuid
		$reply->revId = $reply->postId = UUID::create();
		list( $reply->userId, $reply->userIp, $reply->userWiki ) = self::userFields( $user );
		$reply->origUserId = $reply->userId;
		$reply->origUserIp = $reply->userIp;
		$reply->origUserWiki = wfWikiId();
		$reply->replyToId = $this->postId;
		$reply->setContent( $content, $workflow->getArticleTitle() );
		$reply->changeType = $changeType;
		$reply->setChildren( array() );
		$reply->setDepth( $this->getDepth() + 1 );
		$reply->rootPost = $this->rootPost;

		return $reply;
	}

	/**
	 * @return UUID
	 */
	public function getPostId() {
		return $this->postId;
	}

	/**
	 * Get the user ID of the user who created this post.
	 *
	 * @return integer The user ID
	 */
	public function getCreatorId() {
		return $this->origUserId;
	}

	public function getCreatorWiki() {
		return $this->origUserWiki;
	}

	/**
	 * Get the user ip of the user who created this post if it
	 * was created by an anonymous user
	 *
	 * @return string|null String if an creator is anon, or null if not.
	 */
	public function getCreatorIp() {
		return $this->origUserIp;
	}

	/**
	 * @return boolean
	 */
	public function isTopicTitle() {
		return $this->replyToId === null;
	}

	/**
	 * @param UUID|null $id
	 */
	public function setReplyToId( UUID $id = null ) {
		$this->replyToId = $id;
	}

	/**
	 * @return UUID|null Id of the parent post, or null if this is the root
	 */
	public function getReplyToId() {
		return $this->replyToId;
	}

	/**
	 * @param PostRevision[] $children
	 */
	public function setChildren( array $children ) {
		$this->children = $children;
		if ( $this->rootPost ) {
			// Propagate root post into children.
			$this->setRootPost( $this->rootPost );
		}
	}

	/**
	 * @return PostRevision[]
	 * @throws DataModelException
	 */
	public function getChildren() {
		if ( $this->children === null ) {
			throw new DataModelException( 'Children not loaded for post: ' . $this->postId->getAlphadecimal(), 'process-data' );
		}
		return $this->children;
	}

	/**
	 * @param integer $depth
	 */
	public function setDepth( $depth ) {
		$this->depth = (int)$depth;
	}

	/**
	 * @return integer
	 * @throws DataModelException
	 */
	public function getDepth() {
		if ( $this->depth === null ) {
			throw new DataModelException( 'Depth not loaded for post: ' . $this->postId->getAlphadecimal(), 'process-data' );
		}
		return $this->depth;
	}

	/**
	 * @param PostRevision $root
	 * @deprecated Use PostCollection::getRoot instead
	 */
	public function setRootPost( PostRevision $root ) {
		$this->rootPost = $root;
		if ( $this->children ) {
			// Propagate root post into children.
			foreach ( $this->children as $child ) {
				$child->setRootPost( $root );
			}
		}
	}

	/**
	 * @return PostRevision
	 * @throws DataModelException
	 * @deprecated Use PostCollection::getRoot instead
	 */
	public function getRootPost() {
		if ( $this->isTopicTitle() ) {
			return $this;
		} elseif ( $this->rootPost === null ) {
			$collection = $this->getCollection();
			$root = $collection->getRoot();
			return $root->getLastRevision();
		}
		return $this->rootPost;
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
	 *
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
		$results = $this->descendRecursive( $this->recursiveCallbacks, $this->recursiveResults );
		$this->recursiveResults = end( $results );

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
			return array( $callbacks, $results );
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
			$id = $post->getCreatorId();
			$ip = $post->getCreatorIp();
			// key is used to prevent duplication
			$key = $id ?: $ip;
			// store id, ip, and (@todo) wiki
			$result[$key] = array( $id, $ip );

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

		return $this->registerRecursive( $callback, false, 'descendant-' . $postId->getAlphadecimal() );
	}

	/**
	 * @return string
	 */
	public function getRevisionType() {
		return 'post';
	}

	/**
	 * @return boolean Posts are unformatted if they are title posts, formatted otherwise.
	 */
	public function isFormatted() {
		return !$this->isTopicTitle();
	}

	/**
	 * @param User $user
	 * @return boolean
	 */
	public function isCreator( User $user ) {
		if ( $user->isAnon() ) {
			return false;
		}
		return $user->getId() == $this->getCreatorId();
	}

	/**
	 * @return UUID
	 */
	public function getCollectionId() {
		return $this->getPostId();
	}

	/**
	 * @return PostCollection
	 */
	public function getCollection() {
		return PostCollection::newFromRevision( $this );
	}
}
