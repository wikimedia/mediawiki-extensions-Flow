<?php

namespace Flow\Block;

use Flow\View\History\History;
use Flow\View\History\HistoryRenderer;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\NotificationController;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\Container;
use Flow\Exception\FailCommitException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $topicTitle;
	protected $rootLoader;
	protected $newRevision;
	protected $notification;
	protected $requestedPost = array();

	// POST actions, GET do not need to be listed
	// unrecognized GET actions fallback to 'view'
	protected $supportedActions = array(
		// Standard editing
		'edit-post', 'reply',
		// Moderation
		'moderate-topic',
		'moderate-post', 'hide-post', 'delete-post', 'suppress-post', 'restore-post',
		// Other stuff
		'edit-title',
	);

	/**
	 * @var RevisionActionPermissions $permissions Allows or denies actions to be performed
	 */
	protected $permissions;

	public function __construct( Workflow $workflow, ManagerGroup $storage, NotificationController $notificationController, $root ) {
		parent::__construct( $workflow, $storage, $notificationController );
		if ( $root instanceof PostRevision ) {
			$this->root = $root;
		} elseif ( $root instanceof RootPostLoader ) {
			$this->rootLoader = $root;
		} else {
			throw new InvalidInputException(
				'Expected PostRevision or RootPostLoader, received: ' . is_object( $root ) ? get_class( $root ) : gettype( $root ), 'invalid-input'
			);
		}
	}

	public function init( $action, $user ) {
		parent::init( $action, $user );
		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
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

		case 'suppress-post':
			$this->validateModeratePost( AbstractRevision::MODERATED_SUPPRESSED );
			break;

		case 'restore-post':
			$this->validateModeratePost( 'restore' );
			break;

		case 'edit-post':
			$this->validateEditPost();
			break;

		default:
			throw new InvalidActionException( "Unexpected action: {$this->action}", 'invalid-action' );
		}
	}

	protected function validateEditTitle() {
		if ( $this->workflow->isNew() ) {
			$this->addError( 'content', wfMessage( 'flow-error-no-existing-workflow' ) );
			return;
		}
		if ( !isset( $this->submitted['content'] ) || !is_string( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-title' ) );
			return;
		}
		$this->submitted['content'] = trim( $this->submitted['content'] );
		$len = mb_strlen( $this->submitted['content'] );
		if ( $len === 0 ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-title' ) );
			return;
		}
		if ( $len > PostRevision::MAX_TOPIC_LENGTH ) {
			$this->addError( 'content', wfMessage( 'flow-error-title-too-long', PostRevision::MAX_TOPIC_LENGTH ) );
			return;
		} elseif ( empty( $this->submitted['prev_revision'] ) ) {
			$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
			return;
		} else {
			$topicTitle = $this->loadTopicTitle();
			if ( !$topicTitle ) {
				throw new InvalidInputException( 'No revision associated with workflow?', 'missing-revision' );
			}
			if ( !$this->permissions->isAllowed( $topicTitle, 'edit-title' ) ) {
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
				return;
			} elseif ( $topicTitle->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
				// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
				// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
				// parent we and the submitter think is the latest, our insert will fail.
				// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
				// handing user back to specific dialog indicating race condition
				$this->addError(
					'prev_revision',
					wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $topicTitle->getRevisionId()->getAlphadecimal() ),
					array( 'revision_id' => $topicTitle->getRevisionId()->getAlphadecimal() ) // save current revision ID
				);
				return;
			}
		}

		$this->newRevision = $topicTitle->newNextRevision( $this->user, $this->submitted['content'], 'edit-title' );

		// run through AbuseFilter
		$status = Container::get( 'controller.spamfilter' )->validate( $this->newRevision, $topicTitle, $this->workflow->getArticleTitle() );
		if ( !$status->isOK() ) {
			foreach ( $status->getErrorsArray() as $message ) {
				$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
			}
			return;
		}

		$this->setNotification( 'flow-topic-renamed' );
	}

	protected function validateReply() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-content' ) );
			return;
		} elseif ( !isset( $this->submitted['replyTo'] ) ) {
			$this->addError( 'replyTo', wfMessage( 'flow-error-missing-replyto' ) );
			return;
		}

		$post = $this->loadRequestedPost( $this->submitted['replyTo'] );
		if ( !$post ) {
			return; // loadRequestedPost adds its own errors
		} elseif ( !$this->permissions->isAllowed( $post, 'reply' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		} else {
			$this->newRevision = $post->reply( $this->user, $this->submitted['content'] );

			// run through AbuseFilter
			$status = Container::get( 'controller.spamfilter' )->validate( $this->newRevision, null, $this->workflow->getArticleTitle() );
			if ( !$status->isOK() ) {
				foreach ( $status->getErrorsArray() as $message ) {
					$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
				}
				return;
			}

			$this->setNotification( 'flow-post-reply', array( 'reply-to' => $post ) );
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
			$this->addError( 'post', wfMessage( 'flow-error-missing-postId' ) );
			return;
		}

		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			// loadRequestedPost added its own messages to $this->errors;
			return;
		}
		if ( $post->isTopicTitle() ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-not-a-post' ) );
			return;
		}
		$this->doModerate( $post, $moderationState );
	}

	protected function doModerate( PostRevision $post, $moderationState = null ) {
		// Moderation state supplied in request parameters rather than the action
		if ( $moderationState === null ) {
			$moderationState = $this->submitted['moderationState'];
		}
		// $moderationState should be a string like 'restore', 'suppress', etc.  The exact strings allowed
		// are checked below with $post->isValidModerationState(), but this is checked first otherwise
		// a blank string would restore a post(due to AbstractRevision::MODERATED_NONE === '').
		if ( ! $moderationState ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-state' ) );
			return;
		}
		// BC: 'suppress' used to be called 'censor'
		if ( $moderationState == 'censor' ) {
			$moderationState = 'suppress';
		}

		// By allowing the moderationState to be sourced from $this->submitted['moderationState']
		// we no longer have a unique action name for use with the permissions system.  This rebuilds
		// an action name. e.x. restore-post, restore-topic, suppress-topic, etc.
		$action = $moderationState . ( $post->isTopicTitle() ? "-topic" : "-post" );

		// 'restore' isn't an actual state, it returns a post to unmoderated status

		if ( $moderationState === 'restore' ) {
			$newState = AbstractRevision::MODERATED_NONE;
		} else {
			$newState = $moderationState;
		}

		if ( ! $post->isValidModerationState( $newState ) ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-state' ) );
			return;
		} elseif ( !$this->permissions->isAllowed( $post, $action ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}

		if ( empty( $this->submitted['reason'] ) ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-reason' ) );
			return;
		}

		$reason = $this->submitted['reason'];

		$this->newRevision = $post->moderate( $this->user, $newState, $action, $reason );
		if ( !$this->newRevision ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}

		// run through AbuseFilter
		$status = Container::get( 'controller.spamfilter' )->validate( $this->newRevision, $post, $this->workflow->getArticleTitle() );
		if ( !$status->isOK() ) {
			foreach ( $status->getErrorsArray() as $message ) {
				$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
			}
			return;
		}
	}

	protected function validateEditPost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->addError( 'post', wfMessage( 'flow-error-missing-postId' ) );
			return;
		} elseif ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-content' ) );
			return;
		} elseif ( empty( $this->submitted['prev_revision'] ) ) {
			$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( !$post ) {
			return;
		}
		if ( !$this->permissions->isAllowed( $post, 'edit-post' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		} elseif ( $post->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
			// This is a reasonably effective way to ensure prev revision matches, but for guarantees against race
			// conditions there also exists a unique index on rev_prev_revision in mysql, meaning if someone else inserts against the
			// parent we and the submitter think is the latest, our insert will fail.
			// TODO: Catch whatever exception happens there, make sure the most recent revision is the one in the cache before
			// handing user back to specific dialog indicating race condition
			$this->addError(
				'prev_revision',
				wfMessage( 'flow-error-prev-revision-mismatch' )->params( $this->submitted['prev_revision'], $post->getRevisionId()->getAlphadecimal() ),
				array( 'revision_id' => $post->getRevisionId()->getAlphadecimal() ) // save current revision ID
			);
			return;
		}

		$this->newRevision = $post->newNextRevision( $this->user, $this->submitted['content'], 'edit-post' );

		// run through AbuseFilter
		$status = Container::get( 'controller.spamfilter' )->validate( $this->newRevision, $post, $this->workflow->getArticleTitle() );
		if ( !$status->isOK() ) {
			foreach ( $status->getErrorsArray() as $message ) {
				$this->addError( 'spamfilter', wfMessage( array_shift( $message ), $message ) );
			}
			return;
		}

		$this->setNotification( 'flow-post-edited' );
	}

	public function commit() {
		$this->workflow->updateLastModified();

		switch( $this->action ) {
		case 'reply':
		case 'moderate-topic':
		case 'hide-post':
		case 'delete-post':
		case 'suppress-post':
		case 'restore-post':
		case 'moderate-post':
		case 'edit-title':
		case 'edit-post':
			if ( $this->newRevision === null ) {
				throw new FailCommitException( 'Attempt to save null revision', 'fail-commit' );
			}

			$this->storage->put( $this->newRevision );
			$this->storage->put( $this->workflow );
			$self = $this;
			$newRevision = $this->newRevision;
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
				$renderFunction = function( $templating ) use ( $newRevision ) {
					return $templating->getContent( $newRevision, 'wikitext' );
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
				// $this->topicTitle has already been loaded before in case
				// we've just edited it, so when editing the title, this will
				// be its previous revision (which is what we want - new
				// revision is in ['params']['revision'])
				$this->notification['params']['topic-title'] = $this->loadTopicTitle();
				$this->notificationController->notifyPostChange( $this->notification['type'], $this->notification['params'] );
			}

			return array(
				'new-revision-id' => $this->newRevision->getRevisionId(),
				'render-function' => $renderFunction,
			);

		default:
			throw new InvalidActionException( "Unknown commit action: {$this->action}", 'invalid-action' );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		if ( in_array( $this->action, array( 'post-history', 'topic-history' ) ) ) {
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.history' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.history' ) );
		} else {
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.discussion', 'ext.flow.moderation' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );
		}

		$prefix = '';

		switch( $this->action ) {
		case 'post-history':
			return $prefix . $this->renderPostHistory( $templating, $options, $return );

		case 'topic-history':
			$history = $this->loadTopicHistory();
			$root = $this->loadRootPost();
			if ( !$root ) {
				return '';
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
				'topicTitle' => $this->newRevision ?: $topicTitle, // if already submitted, use submitted revision,
			), $return );

		case 'compare-revisions':
			if ( ! isset( $options['oldRevision'] ) || ! isset( $options['newRevision'] ) ) {
				throw new InvalidInputException( 'Two revisions must be specified to compare them', 'revision-comparison' );
			}

			$oldRevId = UUID::create( $options['oldRevision'] );
			$newRevId = UUID::create( $options['newRevision'] );

			list( $oldRev, $newRev ) = $this->storage->getMulti(
				'PostRevision',
				array(
					$oldRevId,
					$newRevId
				)
			);

			// In theory the backend will return things in increasing PK order
			// (i.e. earlier revision first), but let's be sure.
			if (
				$oldRev->getRevisionId()->getTimestamp() >
				$newRev->getRevisionId()->getTimestamp()
			) {
				$temp = $oldRev;
				$oldRev = $newRev;
				$newRev = $temp;
			}

			if ( ! $oldRev->getPostId()->equals( $newRev->getPostId() ) ) {
				throw new InvalidInputException( 'Attempt to compare revisions of different posts', 'revision-comparison' );
			}

			$templating->getOutput()->addModules( 'ext.flow.history' );

			return $prefix . $templating->render(
				'flow:compare-revisions.html.php',
				array(
					'block' => $this,
					'user' => $this->user,
					'oldRevision' => $oldRev,
					'newRevision' => $newRev,
				), $return
			);
			break;

		default:
			$root = $this->loadRootPost();
			if ( !$root ) {
				return '';
			}

			if ( !isset( $options['topiclist-block'] ) ) {
				$title = $templating->getContent( $root, 'wikitext' );
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
			} elseif ( isset( $options['revId'] ) ) {
				return $this->renderRevision( $templating, $options, $return );
			} else {
				return $prefix . $templating->renderTopic(
					$root,
					$this,
					$return
				);
			}
		}
	}

	protected function renderRevision( Templating $templating, array $options, $return = false ) {
		$postRevision = $this->loadRequestedRevision( $options['revId'] );

		if ( !$postRevision ) {
			return '';
		}

		// @todo Do we perhaps want to show the children that did exist at the time of editing?
		$postRevision->setChildren( array() );

		$prefix = $templating->render(
			'flow:revision-permalink-warning.html.php',
			array(
				'block' => $this,
				'revision' => $postRevision,
			),
			$return
		);

		if ( $postRevision->isTopicTitle() ) {
			return $prefix . $templating->renderTopic(
				$postRevision,
				$this,
				$return
			);
		} else {
			return $prefix . $templating->renderPost(
				$postRevision,
				$this,
				$return
			);
		}
	}

	protected function renderPostHistory( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			$this->addError( 'post', wfMessage( 'flow-error-missing-postId' ) );
			return '';
		}
		$post = $this->loadRequestedPost( $options['postId'] );
		if ( !$post ) {
			return '';
		}

		$topicTitle = $this->loadTopicTitle(); // pre-loaded by loadRequestedPost
		if ( !$this->permissions->isAllowed( $topicTitle, 'view' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return '';
		}

		$history = $this->getHistory( $options['postId'] );

		return $templating->render( "flow:post-history.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'topicTitle' => $this->loadTopicTitle(), // pre-loaded by loadRequestedPost
			'post' => $post,
			'history' => new History( $history ),
			'historyRenderer' => new HistoryRenderer( $templating, $this ),
		) );
	}

	protected function renderEditPost( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			throw new InvalidInputException( 'No postId provided', 'invalid-input' );
		}
		$post = $this->loadRequestedPost( $options['postId'] );
		if ( !$post ) {
			return '';
		}
		if ( !$this->permissions->isAllowed( $post, 'edit-post' ) ) {
			throw new PermissionException( 'Not Allowed', 'insufficient-permission' );
		}
		return $templating->render( "flow:edit-post.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'post' => $this->newRevision ?: $post, // if already submitted, use submitted revision
		), $return );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( isset( $options['postId'] ) ) {
			$rootPost = $this->loadRootPost();
			if ( !$rootPost ) {
				return array();
			}

			$indexDescendant = $rootPost->registerDescendant( $options['postId'] );
			$post = $rootPost->getRecursiveResult( $indexDescendant );
			if ( $post === false ) {
				throw new InvalidInputException( 'Requested postId is not available within post tree', 'invalid-input' );
			}

			if ( !$post ) {
				throw new InvalidInputException( 'Requested post could not be found', 'invalid-input' );
			}

			$res = $this->renderPostAPI( $templating, $post, $options );
			if ( $res === null ) {
				throw new PermissionException( 'Not Allowed', 'insufficient-permission' );
			}
			return array( $res );
		} else {
			$output = $this->renderTopicAPI( $templating, $options );
			if ( $output === null ) {
				throw new PermissionException( 'Not Allowed', 'insufficient-permission' );
			}
			return $output;
		}
	}

	public function renderTopicAPI( Templating $templating, array $options ) {
		$topic = $this->workflow;
		$rootPost = $this->loadRootPost();
		if ( !$rootPost ) {
			return array();
		}

		$output = array(
			'_element' => 'post',
			'title' => $templating->getContent( $rootPost, 'wikitext' ),
			'topic-id' => $topic->getId()->getAlphadecimal(),
		);

		if ( isset( $options['showhistoryfor'] ) ) {
			$options['history'] = array();

			$historyBatch = $this->getHistoryBatch( (array)$options['showhistoryfor'] );

			foreach( $historyBatch as $historyGroup ) {
				foreach( $historyGroup as $historyEntry ) {
					$postId = $historyEntry->getPostId()->getAlphadecimal();
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
		$output['post-id'] = $post->getPostId()->getAlphadecimal();
		$output['revision-id'] = $post->getRevisionId()->getAlphadecimal();
		$contentFormat = $post->getContentFormat();

		// This may force a round trip through parsoid for the wikitext when
		// posts are stored as html, as such it should only be used when
		// actually needed
		if ( isset( $options['contentFormat'] ) ) {
			$contentFormat = $options['contentFormat'];
		}

		if ( $post->isModerated() ) {
			$output['post-moderated'] = 'post-moderated';
		} else {
			$output['content'] = array(
				'*' => $templating->getContent( $post, $contentFormat ),
				'format' => $contentFormat
			);
			$output['user'] = $templating->getCreatorText( $post );
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

		$postId = $post->getPostId()->getAlphadecimal();
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
					'revision-id' => $revision->getRevisionId()->getAlphadecimal(),
					'revision-author' => $templating->getUserText( $revision ),
					'revision-change-type' => $revision->getChangeType(),
				);
			}
		}

		return $output;
	}

	protected function getHistory( $postId ) {
		$history = $this->storage->find(
			'PostRevision',
			array( 'tree_rev_descendant_id' => UUID::create( $postId ) ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( $history ) {
			// get rid of history entries user doesn't have sufficient permissions for
			foreach ( $history as $i => $revision ) {
				// only check against the specific revision, ignoring the most recent
				if ( !$this->permissions->isRevisionAllowed( $revision, 'post-history' ) ) {
					unset( $history[$i] );
				}
			}

			return $history;
		} else {
			throw new InvalidDataException( 'Unable to load post history for post ' . $postId, 'fail-load-history' );
		}
	}

	protected function getHistoryBatch( $postIds ) {
		$searchItems = array();

		// Make list of candidate conditions
		foreach( $postIds as $postId ) {
			$uuid = UUID::create( $postId );
			$searchItems[$uuid->getAlphadecimal()] = array(
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

			$postId = $cur->getPostId()->getAlphadecimal();
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

	public function loadRootPost() {
		if ( $this->root !== null ) {
			return $this->root;
		}

		$rootPost = $this->rootLoader->get( $this->workflow->getId() );

		if ( $this->permissions->isAllowed( $rootPost, 'view' ) ) {
			// topicTitle is same as root, difference is root has children populated to full depth
			return $this->topicTitle = $this->root = $rootPost;
		}

		$this->addError( 'moderation', wfMessage( 'flow-error-not-allowed' ) );

		return null;
	}

	// Loads only the title, as opposed to loadRootPost which gets the entire tree of posts.
	public function loadTopicTitle() {
		if ( $this->workflow->isNew() ) {
			throw new InvalidDataException( 'New workflows do not have any related content', 'missing-topic-title' );
		}
		if ( $this->topicTitle === null ) {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => $this->workflow->getId() ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( !$found ) {
				throw new InvalidDataException( 'Every workflow must have an associated topic title', 'missing-topic-title' );
			}
			$this->topicTitle = reset( $found );

			// this method loads only title, nothing else; otherwise, you're
			// looking for loadRootPost
			$this->topicTitle->setChildren( array() );
			$this->topicTitle->setDepth( 0 );
			$this->topicTitle->setRootPost( $this->topicTitle );

			if ( !$this->permissions->isAllowed( $this->topicTitle, 'view' ) ) {
				$this->topicTitle = null;
				$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			}
		}
		return $this->topicTitle;
	}

	protected function loadTopicHistory() {
		$history = $this->storage->find(
			'PostRevision',
			array( 'topic_root_id' => $this->workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( $history ) {
			// get rid of history entries user doesn't have sufficient permissions for
			foreach ( $history as $i => $revision ) {
				// only check against the specific revision, ignoring the most recent
				if ( !$this->permissions->isRevisionAllowed( $revision, 'topic-history' ) ) {
					unset( $history[$i] );
				}
			}

			return $history;
		} else {
			throw new InvalidDataException( 'Unable to load topic history for topic ' . $this->workflow->getId()->getAlphadecimal(), 'fail-load-history' );
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
				return null;
			}
			$post = $root->getRecursiveResult( $root->registerDescendant( $postId ) );
			if ( !$post ) {
				// The requested postId is not a member of the current workflow
				$this->addError( 'post', wfMessage( 'flow-error-invalid-postId', $postId->getAlphadecimal() ) );
				return null;
			}
		} else {
			// Load the post and its root
			$found = $this->rootLoader->getWithRoot( $postId );
			if ( !$found['post'] || !$found['root'] || !$found['root']->getPostId()->equals( $this->workflow->getId() ) ) {
				$this->addError( 'post', wfMessage( 'flow-error-invalid-postId', $postId->getAlphadecimal() ) );
				return null;
			}
			$this->topicTitle = $topicTitle = $found['root'];
			$post = $found['post'];

			// using the path to the root post, we can know the post's depth
			$rootPath = $this->rootLoader->treeRepo->findRootPath( $postId );
			$post->setDepth( count( $rootPath ) - 1 );
			$post->setRootPost( $found['root'] );
		}

		if ( $this->permissions->isAllowed( $topicTitle, 'view' )
			&& $this->permissions->isAllowed( $post, 'view' ) ) {
			return $post;
		}

		$this->addError( 'moderation', wfMessage( 'flow-error-not-allowed' ) );
		return null;
	}

	protected function loadRequestedRevision( $revisionId ) {
		if ( !$revisionId instanceof UUID ) {
			$revisionId = UUID::create( $revisionId );
		}

		$found = $this->storage->get( 'PostRevision', $revisionId );

		if ( !$found ) {
			throw new InvalidInputException( 'The requested revision could not be found', 'missing-revision' );
		} else if ( !$this->permissions->isAllowed( $found, 'view' ) ) {
			$this->addError( 'moderation', wfMessage( 'flow-error-not-allowed' ) );
			return null;
		}

		// using the path to the root post, we can know the post's depth
		$rootPath = $this->rootLoader->treeRepo->findRootPath( $found->getPostId() );
		$found->setDepth( count( $rootPath ) - 1 );

		return $found;
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
			throw new InvalidInputException( 'Should have found revisions', 'missing-revision' );
		}
		$revId = $post->getRevisionId();
		$rootPost = $post->getRootPost();
		foreach ( $found as $idx => $revision ) {
			if ( $revId->equals( $revision->getRevisionId() ) ) {
				// Because storage returns a new object for every query
				// We need to find $post in the array and replace it
				$found[$idx] = $post;
			} else {
				// Root post needs to propogate from $post to found revisions
				$revision->setRootPost( $rootPost );
			}
		}
		return $found;
	}

	// Somehow the template has to know which post the errors go with
	public function getRepliedTo() {
		return isset( $this->submitted['replyTo'] ) ? $this->submitted['replyTo'] : null;
	}

	public function getAlphadecimalRepliedTo() {
		$repliedTo = $this->getRepliedTo();
		return $repliedTo instanceof UUID ? $repliedTo->getAlphadecimal() : $repliedTo;
	}

	// The prefix used for form data
	public function getName() {
		return 'topic';
	}

	protected function setNotification( $notificationType, array $extraVars = array() ) {
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
