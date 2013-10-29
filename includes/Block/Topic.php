<?php

namespace Flow\Block;

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
use EchoEvent;
use User;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $topicTitle;
	protected $rootLoader;
	protected $newRevision;
	protected $notification;
	protected $requestedPost;

	// POST actions, GET do not need to be listed
	// unrecognized GET actions fallback to 'view'
	protected $supportedActions = array(
		// Standard editing
		'edit-post', 'reply',
		// Moderation
		'moderate-post', 'hide-post', 'delete-post', 'censor-post', 'restore-post',
		// Other stuff
		'hide-topic', 'edit-title',
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
		$this->permissions = new PostActionPermissions( $user );
	}

	protected function validate() {
		switch( $this->action ) {
		case 'edit-title':
			$this->validateEditTitle();
			break;

		case 'reply':
			$this->validateReply();
			break;

		case 'hide-topic':
			// this should be a workflow level action, not implemented per-block
			$this->validateHideTopic();
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
			$this->validateRestorePost();
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
				throw new \Exception( 'No revision associated with workflow?' );
			}
			if ( !$this->permissions->isAllowed( $topicTitle, 'edit-title' ) ) {
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
				return;
			}

			$this->newRevision = $topicTitle->newNextRevision( $this->user, $this->submitted['content'], 'flow-rev-message-edit-title' );

			$this->setNotification(
				'flow-topic-renamed',
				array(
					'old-subject' => $topicTitle->getContent( null, 'wikitext' ),
					'new-subject' => $this->newRevision->getContent( null, 'wikitext' ),
				)
			);
		}
	}

	protected function validateReply() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-error-missing-content' );
		}

		if ( !isset( $this->submitted['replyTo'] ) ) {
			$this->errors['replyTo'] = wfMessage( 'flow-error-missing-replyto' );
		} else {
			$this->submitted['replyTo'] = UUID::create( $this->submitted['replyTo']  );
			$post = $this->storage->get( 'PostRevision', $this->submitted['replyTo'] );
			if ( !$post ) {
				$this->errors['replyTo'] = wfMessage( 'flow-error-invalid-replyto' );
			} elseif ( !$this->permissions->isAllowed( $post, 'reply' ) ) {
				// Or should the check be rolled into the !$post condition?
				$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			} else {
				// TODO: assert post belongs to this tree?  Does it really matter?
				// answer: might not belong, and probably does matter due to inter-wiki interaction
				$this->newRevision = $post->reply( $this->user, $this->submitted['content'] );

				$this->setNotification(
					'flow-post-reply',
					array(
						'reply-to' => $post,
						'content' => $this->submitted['content'],
						'topic-title' => $this->getTitleText(),
					)
				);
			}
		}
	}

	protected function validateHideTopic() {
		if ( !$this->workflow->lock( $this->user ) ) {
			$this->errors['hide-topic'] = wfMessage( 'flow-error-hide-failure' );
		}
	}

	protected function validateModeratePost( $moderationState = null ) {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}

		// Moderation state supplied in parameters
		if ( $moderationState === null ) {
			$moderationState = $this->submitted['moderationState'];
		}

		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-invalid-postId', $this->submitted['postId'] );
			return;
		}

		if ( ! $moderationState ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-invalid-moderation-state' );
			return;
		} elseif ( $moderationState === 'restore' ) {
			$moderationState = '';
		}

		if ( ! $post->isValidModerationState( $moderationState ) ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-invalid-moderation-state' );
			return;
		} elseif ( !$this->permissions->isAllowed( $post, "{$moderationState}-post" ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return;
		}

		$this->newRevision = $post->moderate( $this->user, $moderationState );
		if ( !$this->newRevision ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-not-allowed' );
		}
	}

	protected function validateRestorePost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-invalid-postId' );
			return;
		} elseif ( !$this->permissions->isAllowed( $post, "restore-post" ) ) {
			$this->errors['permissions'] = wfMessage( 'flow-error-not-allowed' );
			return;
		}

		$this->newRevision = $post->restore( $this->user );
		if ( !$this->newRevision ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-not-allowed' );
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
			$this->errors['edit-post'] = wfMessage( 'flow-post-not-found' );
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
				'topic-title' => $this->getTitleText(),
			)
		);
	}

	public function commit() {
		$this->workflow->updateLastModified();

		switch( $this->action ) {
		case 'reply':
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
			$self = $this;
			$newRevision = $this->newRevision;
			$rootPost = $this->loadRootPost();

			$newRevision->setChildren( array() );

			// FIXME special case
			if ( $this->action == 'edit-title' ) {
				$renderFunction = function( $templating ) use ( $newRevision ) {
					return $newRevision->getContent( null, 'wikitext' );
				};
			} else {
				$renderFunction = function( $templating ) use ( $self, $newRevision, $rootPost ) {
					return $templating->renderPost( $newRevision, $self, $rootPost );
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

		case 'delete-topic':
			$this->storage->put( $this->workflow );

			return 'success';

		default:
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModules( 'ext.flow.discussion' );
		$prefix = '';

		switch( $this->action ) {
		case 'post-history':
			return $prefix . $this->renderPostHistory( $templating, $options, $return );

		case 'topic-history':
			$history = $this->loadTopicHistory();
			if ( !$this->permissions->isAllowed( reset( $history ), 'post-history' ) ) {
				throw new \MWException( 'Not Allowed' );
			}
			return $prefix . $templating->render( "flow:topic-history.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'history' => $this->loadTopicHistory(),
			), $return );

		case 'edit-post':
			return $prefix . $this->renderEditPost( $templating, $options, $return );

		case 'edit-title':
			$topicTitle = $this->loadTopicTitle();
			if ( !$this->permissions->isAllowed( $topicTitle, 'edit-post' ) ) {
				throw new \MWException( 'Not Allowed' );
			}
			return $prefix . $templating->render( "flow:edit-title.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'topicTitle' => $this->loadTopicTitle(),
			), $return );

		default:
			$root = $this->loadRootPost();

			if ( !$this->permissions->isAllowed( $root, 'view' ) ) {
				throw new \MWException( 'Not Allowed' );
			}
			if ( ! isset( $options['topiclist-block'] ) ) {
				$prefix = $templating->render(
					'flow:topic-permalink-warning.html.php',
					array(
						'block' => $this,
					),
					$return
				);
			}

			if ( isset( $options['postId'] ) ) {
				$indexDescendant = $root->registerDescendant( $options['postId'] );
				$post = $root->getRecursiveResult( $indexDescendant );
				if ( $post === null ) {
					throw new \MWException( 'Requested postId is not available within post tree' );
				}

				return $prefix . $templating->renderPost(
					$post,
					$this,
					$return
				);
			} else {
				return $prefix . $templating->renderTopic(
					$root,
					$this,
					$return
				);
			}
		}
	}

	protected function renderPostHistory( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			throw new \Exception( 'No postId provided' );
		}
		$history = $this->getHistory( $options['postId'] );
		if ( !$this->permissions->isAllowed( reset( $history ), 'post-history' ) ) {
			throw new \MWException( 'Not Allowed' );
		}
		return $templating->render( "flow:post-history.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'history' => $this->getHistory( $options['postId'] ),
		), $return );
	}

	protected function renderEditPost( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			throw new \Exception( 'No postId provided' );
		}
		$post = $this->loadRequestedPost( $options['postId'] );
		if ( !$this->permissions->isAllowed( $post, 'edit-post' ) ) {
			throw new \MWException( 'Not Allowed' );
		}
		return $templating->render( "flow:edit-post.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'post' => $this->loadRequestedPost( $options['postId'] ),
		), $return );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( isset( $options['postId'] ) ) {
			$rootPost = $this->loadRootPost();

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

	public function renderTopicAPI ( Templating $templating, array $options ) {
		$output = array();
		$rootPost = $this->loadRootPost();
		$topic = $this->workflow;

		if ( !$this->permissions->isAllowed( $rootPost, 'view' ) ) {
			throw new \MWException( 'Not Allowed' );
		}
		$output = array(
			'_element' => 'post',
			'title' => $rootPost->getContent( null, 'wikitext' ),
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
				'*' => $post->getContent( null, $contentFormat ),
				'format' => $contentFormat
			);
			$output['user'] = $post->getCreator()->getName();
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
			$output['revisions'] = $this->getAPIHistory( $postId, $options['history'][$postId] );
		}

		return $output;
	}

	protected function getAPIHistory( /*string*/ $postId, array $history ) {
		$output = array();

		$output['_element'] = 'revision';
		$output['post-id'] = $postId;

		foreach( $history as $revision ) {
			if ( $this->permissions->isAllowed( $revision, 'view' ) ) {
				$output[] = array(
					'revision-id' => $revision->getRevisionId()->getHex(),
					'revision-author' => $revision->getUserText(),
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
		// topicTitle is same as root, difference is root has children populated to full depth
		return $this->topicTitle = $this->root = $this->rootLoader->get( $this->workflow->getId() );
	}

	// Loads only the title, as opposed to loadRootPost which gets the entire tree of posts.
	protected function loadTopicTitle() {
		if ( $this->topicTitle === null ) {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => $this->workflow->getId() ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( $found ) {
				$this->topicTitle = reset( $found );
			}
		}
		return $this->topicTitle;
	}

	public function getTitleText() {
		return $this->loadTopicTitle()->getContent( null, 'wikitext' );
	}

	protected function loadTopicHistory() {
		$found = $this->storage->find(
			'PostRevision',
			array( 'topic_root' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( $found ) {
			return $found;
		} else {
			throw new \MWException( "Unable to load topic history for topic " . $this->workflow->getId()->getHex() );
		}
	}

	protected function loadRequestedPost( $postId ) {
		if ( !isset( $this->requestedPost[$postId] ) ) {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => UUID::create( $postId ) ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( $found ) {
				$this->requestedPost[$postId] = reset( $found );
			} else {
				// meh, signals that its not found, dont look again
				$this->requestedPost[$postId] = false;
			}
		}
		// catches the === false and returns null as expected
		return $this->requestedPost[$postId] ?: null;
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
