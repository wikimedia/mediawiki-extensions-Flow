<?php

namespace Flow\Block;

use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\NotificationController;
use Flow\PostActionPermissions;
use Flow\Templating;
use Flow\Container;
use EchoEvent;
use User;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $topicTitle;
	protected $rootLoader;
	protected $newRevision;
	protected $relatedRevisions = array();
	protected $notification;
	protected $requestedPost = array();

	// POST actions, GET do not need to be listed
	// unrecognized GET actions fallback to 'view'
	protected $supportedActions = array(
		// Standard editing
		'edit-post', 'reply',
		// Moderation
		'moderate-topic',
		'moderate-post', 'hide-post', 'delete-post', 'censor-post', 'restore-post',
		// Other stuff
		'edit-title',
	);

	/**
	 * @var PostActionPermissions $permissions Allows or denies actions to be performed
	 */
	protected $permissions;

	public function __construct( Workflow $workflow, ManagerGroup $storage, NotificationController $notificationController, $root ) {
		parent::__construct( $workflow, $storage, $notificationController );
		if ( $root instanceof PostRevision ) {
			$this->root = $root;
		} elseif ( $root instanceof RootPostLoader ) {
			$this->rootLoader = $root;
		} else {
			throw new \InvalidArgumentException(
				'Expected PostRevision or RootPostLoader, received: ' . is_object( $root ) ? get_class( $root ) : gettype( $root )
			);
		}
	}

	public function init( $action, $user ) {
		parent::init( $action, $user );

		// @todo: I don't like pulling stuff from container in here, improve this some day
		$this->permissions = new PostActionPermissions( Container::get( 'flow_actions' ), $user );
	}

	protected function validate() {
		switch( $this->action ) {
		case 'edit-title':
			$this->validateEditTitle();
			break;

		case 'reply':
			$this->validateReply();
			break;

		case 'moderate-topic':
			$this->validateModerateTopic();
			break;

		case 'moderate-post':
			$this->validateModeratePost();
			break;

		case 'hide-post':
			$this->validateModeratePost( AbstractRevision::MODERATED_HIDDEN );
			break;

		case 'delete-post':
			$this->validateModeratePost( AbstractRevision::MODERATED_DELETED );
			break;

		case 'censor-post':
			$this->validateModeratePost( AbstractRevision::MODERATED_CENSORED );
			break;

		case 'restore-post':
			$this->validateModeratePost( 'restore' );
			break;

		case 'edit-post':
			$this->validateEditPost();
			break;

		default:
			throw new \MWException( "Unexpected action: {$this->action}" );
		}
	}

	protected function validateEditTitle() {
		if ( $this->workflow->isNew() ) {
			$this->errors['content'] = wfMessage( 'flow-no-existing-workflow' );
		} elseif ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-title-content' );
		} else {
			$topicTitle = $this->loadTopicTitle();
			if ( !$topicTitle ) {
				throw new \MWException( 'No revision associated with workflow?' );
			}
			if ( !$this->permissions->isAllowed( $topicTitle, 'edit-title' ) ) {
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
				return;
			}

			$this->newRevision = $topicTitle->newNextRevision( $this->user, $this->submitted['content'], 'edit-title' );

			$this->setNotification(
				'flow-topic-renamed',
				array(
					'old-subject' => $topicTitle->getContent( 'wikitext' ),
					'new-subject' => $this->newRevision->getContent( 'wikitext' ),
				)
			);
		}
	}

	protected function validateReply() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-error-missing-content' );
			return;
		} elseif ( !isset( $this->submitted['replyTo'] ) ) {
			$this->errors['replyTo'] = wfMessage( 'flow-error-missing-replyto' );
			return;
		}

		$post = $this->loadRequestedPost( $this->submitted['replyTo'] );
		if ( !$post ) {
			return; // loadRequestedPost adds its own errors
		} elseif ( !$this->permissions->isAllowed( $post, 'reply' ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
		} else {
			$this->newRevision = $post->reply( $this->user, $this->submitted['content'] );

			$this->setNotification(
				'flow-post-reply',
				array(
					'reply-to' => $post,
					'content' => $this->submitted['content'],
					'topic-title' => $this->loadTopicTitle()->getContent( 'wikitext' ),
				)
			);
		}
	}

	protected function validateModerateTopic( $moderationState = null ) {
		$root = $this->loadRootPost();
		if ( !$root ) {
			return;
		}

		$this->doModerate( $root, $moderationState );
	}

	protected function validateModeratePost( $moderationState = null ) {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}

		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			// loadRequestedPost added its own messages to $this->errors;
			return;
		}
		if ( $post->isTopicTitle() ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-not-a-post' );
			return;
		}
		$this->doModerate( $post, $moderationState );
	}

	protected function doModerate( PostRevision $post, $moderationState = null ) {
		// Moderation state supplied in request parameters rather than the action
		if ( $moderationState === null ) {
			$moderationState = $this->submitted['moderationState'];
		}
		// $moderationState should be a string like 'restore', 'censor', etc.  The exact strings allowed
		// are checked below with $post->isValidModerationState(), but this is checked first otherwise
		// a blank string would restore a post(due to AbstractRevision::MODERATED_NONE === '').
		if ( ! $moderationState ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-invalid-moderation-state' );
			return;
		}

		// By allowing the moderationState to be sourced from $this->submitted['moderationState']
		// we no longer have a unique action name for use with the permissions system.  This rebuilds
		// an action name. e.x. restore-post, restore-topic, censor-topic, etc.
		$action = $moderationState . ( $post->isTopicTitle() ? "-topic" : "-post" );

		// 'restore' isn't an actual state, it returns a post to unmoderated status

		if ( $moderationState === 'restore' ) {
			$newState = AbstractRevision::MODERATED_NONE;
		} else {
			$newState = $moderationState;
		}

		if ( ! $post->isValidModerationState( $newState ) ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-invalid-moderation-state' );
			return;
		} elseif ( !$this->permissions->isAllowed( $post, $action ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return;
		}

		if ( empty( $this->submitted['reason'] ) ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-invalid-moderation-reason' );
			return;
		}
		if ( $post->needsModerateHistorical( $newState ) ) {
			$this->relatedRevisions = $this->loadHistorical( $post );
		}

		$this->newRevision = $post->moderate( $this->user, $newState, $action, $this->relatedRevisions );
		if ( !$this->newRevision ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-not-allowed' );
		} else {
			// @todo: would be nice to get this logging into a LifecycleHandler
			$logger = Container::get( 'logger' );
			if ( $logger->canLog( $post, $action ) ) {
				$logger->log(
					$post,
					$action,
					$this->submitted['reason'],
					$this->getWorkflow(),
					array(
						$this->getName() . '[postId]' => $post->getPostId()->getHex(),
					)
				);
			}
		}
	}

	protected function validateEditPost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['edit-post'] = wfMessage( 'flow-no-post-provided' );
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			return;
		}
		if ( !$this->permissions->isAllowed( $post, 'edit-post' ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return;
		}

		$this->newRevision = $post->newNextRevision( $this->user, $this->submitted['content'], 'flow-edit-post' );
		$this->setNotification(
			'flow-post-edited',
			array(
				'content' => $this->submitted['content'],
				'topic-title' => $this->loadTopicTitle()->getContent( 'wikitext' ),
			)
		);
	}

	public function commit() {
		$this->workflow->updateLastModified();

		switch( $this->action ) {
		case 'reply':
		case 'moderate-topic':
		case 'hide-post':
		case 'delete-post':
		case 'censor-post':
		case 'restore-post':
		case 'moderate-post':
		case 'edit-title':
		case 'edit-post':
			if ( $this->newRevision === null ) {
				throw new \MWException( 'Attempt to save null revision' );
			}
			$this->storage->put( $this->newRevision );
			$this->storage->put( $this->workflow );
			// These are moderated historical revisions of $this->newRevision
			foreach ( $this->relatedRevisions as $revision ) {
				$this->storage->put( $revision );
			}
			$self = $this;
			$newRevision = $this->newRevision;
			$user = $this->user;
			$rootPost = $this->loadRootPost();

			// If no context was loaded render the post in isolation
			// @todo make more explicit
			try {
				$newRevision->getChildren();
			} catch ( \MWException $e ) {
				$newRevision->setChildren( array() );
			}


			// FIXME special case
			if ( $this->action == 'edit-title' ) {
				$renderFunction = function( $templating ) use ( $newRevision, $user ) {
					return $templating->getContent( $newRevision, 'wikitext', $user );
				};
			} elseif ( $this->action === 'moderate-topic' ) {
				$renderFunction = function( $templating ) use ( $self, $newRevision ) {
					return $templating->renderTopic( $newRevision, $self );
				};
			} else {
				$renderFunction = function( $templating ) use ( $self, $newRevision, $rootPost ) {
					return $templating->renderPost( $newRevision, $self );
				};
			}

			if ( is_array( $this->notification ) ) {
				$this->notification['params']['revision'] = $this->newRevision;

				$this->notificationController->notifyPostChange( $this->notification['type'], $this->notification['params'] );
			}

			return array(
				'new-revision-id' => $this->newRevision->getRevisionId(),
				'render-function' => $renderFunction,
			);

		default:
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModuleStyles( array( 'ext.flow.discussion', 'ext.flow.moderation' ) );
		$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );
		$prefix = '';

		switch( $this->action ) {
		case 'post-history':
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
			return $prefix . $this->renderPostHistory( $templating, $options, $return );

		case 'topic-history':
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
			$history = $this->loadTopicHistory();
			if ( !$this->permissions->isAllowed( reset( $history ), 'post-history' ) ) {
				throw new \MWException( 'Not Allowed' );
			}

			$root = $this->loadRootPost();
			if ( !$root ) {
				return;
			}

			return $prefix . $templating->render( "flow:topic-history.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'root' => $root,
				'history' => new History( $history ),
				'historyRenderer' => new HistoryRenderer( $templating, $this ),
			), $return );

		case 'edit-post':
			return $prefix . $this->renderEditPost( $templating, $options, $return );

		case 'edit-title':
			$topicTitle = $this->loadTopicTitle();
			if ( !$this->permissions->isAllowed( $topicTitle, 'edit-title' ) ) {
				return $prefix . $templating->render( 'flow:error-permissions.html.php' );
			}
			return $prefix . $templating->render( "flow:edit-title.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'topicTitle' => $topicTitle,
			), $return );

		default:
			$root = $this->loadRootPost();
			if ( !$root ) {
				return;
			}

			if ( !isset( $options['topiclist-block'] ) ) {
				$title = $templating->getContent( $root, 'wikitext', $this->user );
				$templating->getOutput()->setHtmlTitle( $title );
				$templating->getOutput()->setPageTitle( $title );

				$prefix = $templating->render(
					'flow:topic-permalink-warning.html.php',
					array(
						'block' => $this,
					),
					$return
				);
			}
			if ( !$this->permissions->isAllowed( $root, 'view' ) ) {
				return $prefix . $templating->render( 'flow:error-permissions.html.php' );
			}

			return $prefix . $templating->renderTopic(
				$root,
				$this,
				$return
			);
		}
	}

	protected function renderPostHistory( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			$this->errors['post'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}
		$post = $this->loadRequestedPost( $options['postId'] );
		if ( !$post ) {
			return;
		}
		if ( !$this->permissions->isAllowed( $post, 'post-history' ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return;
		}

		return $templating->render( "flow:post-history.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'topicTitle' => $this->loadTopicTitle(), // pre-loaded by loadRequestedPost
			'post' => $post,
			'history' => new History( $this->getHistory( $options['postId'] ) ),
			'historyRenderer' => new HistoryRenderer( $templating, $this ),
		) );
	}

	protected function renderEditPost( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			throw new \MWException( 'No postId provided' );
		}
		$post = $this->loadRequestedPost( $options['postId'] );
		if ( !$post ) {
			return;
		}
		if ( !$this->permissions->isAllowed( $post, 'edit-post' ) ) {
			throw new \MWException( 'Not Allowed' );
		}
		return $templating->render( "flow:edit-post.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'post' => $post,
		), $return );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( isset( $options['postId'] ) ) {
			$rootPost = $this->loadRootPost();
			if ( !$rootPost ) {
				return;
			}

			$indexDescendant = $rootPost->registerDescendant( $options['postId'] );
			$post = $rootPost->getRecursiveResult( $indexDescendant );
			if ( $post === false ) {
				throw new \MWException( 'Requested postId is not available within post tree' );
			}

			if ( ! $post ) {
				throw new \MWException( "Requested post could not be found" );
			}

			$res = $this->renderPostAPI( $templating, $post, $options );
			if ( $res === null ) {
				throw new \MWException( 'Not Allowed' );
			}
			return array( $res );
		} else {
			$output = $this->renderTopicAPI( $templating, $options );
			if ( $output === null ) {
				throw new \MWException( 'Not Allowed' );
			}
			return $output;
		}
	}

	public function renderTopicAPI( Templating $templating, array $options ) {
		$output = array();
		$topic = $this->workflow;
		$rootPost = $this->loadRootPost();
		if ( !$rootPost ) {
			return;
		}

		$output = array(
			'_element' => 'post',
			'title' => $templating->getContent( $rootPost, 'wikitext', $this->user ),
			'topic-id' => $topic->getId()->getHex(),
		);

		if ( isset( $options['showhistoryfor'] ) ) {
			$options['history'] = array();

			$historyBatch = $this->getHistoryBatch( (array)$options['showhistoryfor'] );

			foreach( $historyBatch as $historyGroup ) {
				foreach( $historyGroup as $historyEntry ) {
					$postId = $historyEntry->getPostId()->getHex();
					if ( ! isset( $options['history'][$postId] ) ) {
						$options['history'][$postId] = array();
					}

					$options['history'][$postId][] = $historyEntry;
				}
			}
		}

		if ( isset( $options['render'] ) ) {
			$output['rendered'] = $templating->renderTopic( $rootPost, $this, true );
		}

		foreach( $rootPost->getChildren() as $child ) {
			$res = $this->renderPostAPI( $templating, $child, $options );
			if ( $res !== null ) {
				$output[] = $res;
			}
		}

		return $output;
	}

	protected function renderPostAPI( Templating $templating, PostRevision $post, array $options ) {
		if ( !$this->permissions->isAllowed( $post, 'view' ) ) {
			// we have to return null, or we would have to duplicate this call when rendering children.
			// callers must check for null and do as appropriate
			return null;
		}

		$output = array();
		$output['post-id'] = $post->getPostId()->getHex();
		$contentFormat = 'wikitext';

		if ( isset( $options['contentFormat'] ) ) {
			$contentFormat = $options['contentFormat'];
		}

		if ( $post->isModerated() ) {
			$output['post-moderated'] = 'post-moderated';
		} else {
			$output['content'] = array(
				'*' => $templating->getContent( $post, $contentFormat, $this->user ),
				'format' => $contentFormat
			);
			$output['user'] = $post->getCreatorName();
		}

		if ( ! isset( $options['no-children'] ) ) {
			$children = array( '_element' => 'post' );

			foreach( $post->getChildren() as $child ) {
				$res = $this->renderPostAPI( $templating, $child, $options );
				if ( $res !== null ) {
					$children[] = $res;
				}
			}

			if ( count( $children ) > 1 ) {
				$output['replies'] = $children;
			}
		}

		$postId = $post->getPostId()->getHex();
		if ( isset( $options['history'][$postId] ) ) {
			$output['revisions'] = $this->getAPIHistory( $templating, $postId, $options['history'][$postId] );
		}

		return $output;
	}

	protected function getAPIHistory( Templating $templating, /*string*/ $postId, array $history ) {
		$output = array();

		$output['_element'] = 'revision';
		$output['post-id'] = $postId;

		foreach( $history as $revision ) {
			if ( $this->permissions->isAllowed( $revision, 'view' ) ) {
				$output[] = array(
					'revision-id' => $revision->getRevisionId()->getHex(),
					'revision-author' => $templating->getUserText( $revision ),
					'revision-change-type' => $revision->getChangeType(),
				);
			}
		}

		return $output;
	}

	protected function getHistory( $postId ) {
		return $this->storage->find(
			'PostRevision',
			array( 'tree_rev_descendant_id' => UUID::create( $postId ) ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
	}

	protected function getHistoryBatch( $postIds ) {
		$searchItems = array();

		// Make list of candidate conditions
		foreach( $postIds as $postId ) {
			$uuid = UUID::create( $postId );
			$searchItems[$uuid->getHex()] = array(
				'tree_rev_descendant_id' => $uuid,
			);
		}

		// Filter conditions so that only relevant ones are requested
		$searchConditions = array();
		$traversalQueue = array( $this->root );

		while( count( $traversalQueue ) > 0 ) {
			$cur = array_shift( $traversalQueue );

			foreach( $cur->getChildren() as $child ) {
				array_push( $traversalQueue, $child );
			}

			$postId = $cur->getPostId()->getHex();
			if ( isset( $searchItems[$postId] ) ) {
				$searchConditions[] = $searchItems[$postId];
			}
		}

		if ( count( $searchConditions ) === 0 ) {
			return array();
		}

		return $this->storage->findMulti(
			'PostRevision',
			$searchConditions,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
	}

	protected function loadRootPost() {
		if ( $this->root !== null ) {
			return $this->root;
		}

		$rootPost = $this->rootLoader->get( $this->workflow->getId() );

		if ( $this->permissions->isAllowed( $rootPost, 'view' ) ) {
			// topicTitle is same as root, difference is root has children populated to full depth
			return $this->topicTitle = $this->root = $rootPost;
		}

		$this->errors['moderation'] = wfMessage( 'flow-error-not-allowed' );
	}

	// Loads only the title, as opposed to loadRootPost which gets the entire tree of posts.
	protected function loadTopicTitle() {
		if ( $this->workflow->isNew() ) {
			throw new \MWException( 'New workflows do not have any related content' );
		}
		if ( $this->topicTitle === null ) {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => $this->workflow->getId() ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( !$found ) {
				throw new \MWException( 'Every workflow must have an associated topic title' );
			}
			$this->topicTitle = reset( $found );
			if ( !$this->permissions->isAllowed( $this->topicTitle, 'view' ) ) {
				$this->topicTitle = null;
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			}
		}
		return $this->topicTitle;
	}

	protected function loadTopicHistory() {
		$found = $this->storage->find(
			'PostRevision',
			array( 'topic_root_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( $found ) {
			return $found;
		} else {
			throw new \MWException( "Unable to load topic history for topic " . $this->workflow->getId()->getHex() );
		}
	}

	/**
	 * Loads the post referenced by $postId. Returns null when:
	 *    $postId does not belong to the workflow
	 *    The user does not have view access to the topic title
	 *    The user does not have view access to the referenced post
	 * All these conditions add a relevant error message to $this->errors when returning null
	 *
	 * @param UUID|string $postId The post being requested
	 * @return PostRevision|null
	 */
	protected function loadRequestedPost( $postId ) {
		if ( !$postId instanceof UUID ) {
			$postId = UUID::create( $postId );
		}

		if ( $this->rootLoader === null ) {
			// Since there is no root loader the full tree is already loaded
			$topicTitle = $root = $this->loadRootPost();
			if ( !$topicTitle ) {
				return;
			}
			$post = $root->getRecursiveResult( $root->registerDescendant( $postId ) );
			if ( !$post ) {
				// The requested postId is not a member of the current workflow
				$this->errors['post'] = wfMessage( 'flow-error-invalid-postId', $postId->getHex() );
				return;
			}
		} else {
			// Load the post and its root
			$found = $this->rootLoader->getWithRoot( $postId );
			if ( !$found['post'] || !$found['root'] || !$found['root']->getPostId()->equals( $this->workflow->getId() ) ) {
				$this->errors['post'] = wfMessage( 'flow-error-invalid-postId', $postId->getHex() );
				return;
			}
			$this->topicTitle = $topicTitle = $found['root'];
			$post = $found['post'];

			// using the path to the root post, we can know the post's depth
			$rootPath = $this->rootLoader->treeRepo->findRootPath( $postId );
			$post->setDepth( count( $rootPath ) );
		}

		if ( $this->permissions->isAllowed( $topicTitle, 'view' )
			&& $this->permissions->isAllowed( $post, 'view' ) ) {
			return $post;
		}

		$this->errors['moderation'] = wfMessage( 'flow-error-not-allowed' );
	}

	protected function loadHistorical( PostRevision $post ) {
		if ( $post->isFirstRevision() ) {
			return array();
		}

		$found = $this->storage->find(
			'PostRevision',
			array( 'tree_rev_descendant_id' => $post->getPostId() )
		);
		if ( !$found ) {
			throw new \MWException( 'Should have found revisions' );
		}
		// Because storage returns a new object for every query
		// We need to find $post in the array and replace it
		$revId = $post->getRevisionId();
		foreach ( $found as $idx => $revision ) {
			if ( $revId->equals( $revision->getRevisionId() ) ) {
				$found[$idx] = $post;
				break;
			}
		}
		return $found;
	}

	// Somehow the template has to know which post the errors go with
	public function getRepliedTo() {
		return isset( $this->submitted['replyTo'] ) ? $this->submitted['replyTo'] : null;
	}

	public function getHexRepliedTo() {
		$repliedTo = $this->getRepliedTo();
		return $repliedTo instanceof UUID ? $repliedTo->getHex() : $repliedTo;
	}

	// The prefix used for form data
	public function getName() {
		return 'topic';
	}

	protected function setNotification( $notificationType, array $extraVars ) {
		$this->notification = array(
				'type' => $notificationType,
				'params' => $extraVars + array(
					'topic-workflow' => $this->workflow,
					'title' => $this->workflow->getArticleTitle(),
					'user' => $this->user,
				)
			);
	}
}
