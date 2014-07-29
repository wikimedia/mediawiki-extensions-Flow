<?php

namespace Flow\Block;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\Exception\FailCommitException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\Parsoid\Utils;
use Flow\RevisionActionPermissions;
use Flow\Templating;

class TopicBlock extends AbstractBlock {

	/**
	 * @var PostRevision|null
	 */
	protected $root;

	/**
	 * @var PostRevision|null
	 */
	protected $topicTitle;

	/**
	 * @var RootPostLoader|null
	 */
	protected $rootLoader;

	/**
	 * @var PostRevision|null
	 */
	protected $newRevision;

	/**
	 * @var array
	 */
	protected $notification;

	/**
	 * @var array
	 */
	protected $requestedPost = array();

	protected $supportedPostActions = array(
		// Standard editing
		'edit-post', 'reply',
		// Moderation
		'moderate-topic',
		'moderate-post',
		// Close or open topic
		'close-open-topic',
		// Other stuff
		'edit-title',
	);

	protected $supportedGetActions = array(
		'reply', 'view', 'history', 'edit-post', 'edit-title', 'compare-post-revisions', 'single-view',
		'view-topic', 'view-post',
		'moderate-topic', 'moderate-post', 'close-open-topic',
	);

	// @Todo - fill in the template names
	protected $templates = array(
		'single-view' => 'single_view',
		'view' => '',
		'reply' => 'reply',
		'history' => 'history',
		'edit-post' => 'edit_post',
		'edit-title' => 'edit_title',
		'compare-post-revisions' => 'diff_view',
		'moderate-topic' => 'moderate_topic',
		'moderate-post' => 'moderate_post',
		'close-open-topic' => 'close',
	);

	protected $requiresWikitext = array( 'edit-post', 'edit-title', 'close-open-topic' );

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
		// If the topic is closed, the only allowed action is to reopen it
		$topicTitle = $this->loadTopicTitle();
		if ( $topicTitle ) {
			if (
				$topicTitle->isClosed()
				&& (
					$this->action !== 'close-open-topic'
					|| $this->submitted['moderationState'] !== 'reopen'
				)
			) {
				$this->addError( 'moderate', wfMessage( 'flow-error-topic-is-closed' ) );
			}
		}

		switch( $this->action ) {
		case 'edit-title':
			$this->validateEditTitle();
			break;

		case 'reply':
			$this->validateReply();
			break;

		case 'moderate-topic':
		case 'close-open-topic':
			$this->validateModerateTopic();
			break;

		case 'moderate-post':
			$this->validateModeratePost();
			break;

		case 'restore-post':
			// @todo still necessary?
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
		}
		if ( empty( $this->submitted['prev_revision'] ) ) {
			$this->addError( 'prev_revision', wfMessage( 'flow-error-missing-prev-revision-identifier' ) );
			return;
		}
		$topicTitle = $this->loadTopicTitle();
		if ( !$topicTitle ) {
			throw new InvalidInputException( 'No revision associated with workflow?', 'missing-revision' );
		}
		if ( !$this->permissions->isAllowed( $topicTitle, 'edit-title' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}
		if ( $topicTitle->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
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

		$this->newRevision = $topicTitle->newNextRevision( $this->user, $this->submitted['content'], 'edit-title' );
		if ( !$this->checkSpamFilters( $topicTitle, $this->newRevision ) ) {
			return;
		}

		$this->setNotification( 'flow-topic-renamed' );
		$this->notificationController->subscribeToWorkflow( $this->user, $this->workflow );
	}

	protected function validateReply() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-content' ) );
			return;
		}
		if ( !isset( $this->submitted['replyTo'] ) ) {
			$this->addError( 'replyTo', wfMessage( 'flow-error-missing-replyto' ) );
			return;
		}

		$post = $this->loadRequestedPost( $this->submitted['replyTo'] );
		if ( !$post ) {
			return; // loadRequestedPost adds its own errors
		}
		if ( !$this->permissions->isAllowed( $post, 'reply' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}
		$this->newRevision = $post->reply( $this->workflow, $this->user, $this->submitted['content'] );
		if ( !$this->checkSpamFilters( null, $this->newRevision ) ) {
			return;
		}

		$this->setNotification( 'flow-post-reply', array( 'reply-to' => $post ) );
		$this->notificationController->subscribeToWorkflow( $this->user, $this->workflow );
	}

	protected function validateModerateTopic() {
		$root = $this->loadRootPost();
		if ( !$root ) {
			return;
		}

		$this->doModerate( $root );
	}

	protected function validateModeratePost() {
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
		$this->doModerate( $post );
	}

	protected function doModerate( PostRevision $post ) {
		if ( $this->submitted['moderationState'] === 'close' && $post->isModerated() ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-close-moderated-post' ) );
			return;
		}

		// Moderation state supplied in request parameters
		$moderationState = isset( $this->submitted['moderationState'] )
			? $this->submitted['moderationState']
			: null;

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

		// these all just mean set to no moderation, it returns a post to unmoderated status
		$allowedRestoreAliases = array( 'reopen', 'unhide', 'undelete', 'unsuppress' );
		if ( in_array( $moderationState, $allowedRestoreAliases ) ) {
			$moderationState = 'restore';
		}
		// By allowing the moderationState to be sourced from $this->submitted['moderationState']
		// we no longer have a unique action name for use with the permissions system.  This rebuilds
		// an action name. e.x. restore-post, restore-topic, suppress-topic, etc.
		$action = $moderationState . ( $post->isTopicTitle() ? "-topic" : "-post" );

		if ( $moderationState === 'restore' ) {
			$newState = AbstractRevision::MODERATED_NONE;
		} else {
			$newState = $moderationState;
		}

		if ( ! $post->isValidModerationState( $newState ) ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-state' ) );
			return;
		}
		if ( !$this->permissions->isAllowed( $post, $action ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}

		if ( empty( $this->submitted['reason'] ) ) {
			// If a summary is provided instead, parse the content and truncate it
			if ( !empty( $this->submitted['summary'] ) ) {
				// @todo there might be a better wikitext->plaintext method
				$this->submitted['reason'] = Utils::htmlToPlaintext(
					Utils::convert(
						'wikitext',
						'html',
						$this->submitted['summary'],
						$this->workflow->getArticleTitle()
					),
					255
				);
			}
			if ( empty( $this->submitted['reason'] ) ) {
				$this->addError( 'moderate', wfMessage( 'flow-error-invalid-moderation-reason' ) );
				return;
			}
		}

		$reason = $this->submitted['reason'];

		$this->newRevision = $post->moderate( $this->user, $newState, $action, $reason );
		if ( !$this->newRevision ) {
			$this->addError( 'moderate', wfMessage( 'flow-error-not-allowed' ) );
			return;
		}
	}

	protected function validateEditPost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->addError( 'post', wfMessage( 'flow-error-missing-postId' ) );
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->addError( 'content', wfMessage( 'flow-error-missing-content' ) );
			return;
		}
		if ( empty( $this->submitted['prev_revision'] ) ) {
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
		}
		if ( $post->getRevisionId()->getAlphadecimal() !== $this->submitted['prev_revision'] ) {
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
		if ( !$this->checkSpamFilters( $post, $this->newRevision ) ) {
			return;
		}

		$this->setNotification( 'flow-post-edited' );
		$this->notificationController->subscribeToWorkflow( $this->user, $this->workflow );
	}

	public function commit() {
		$this->workflow->updateLastModified();

		switch( $this->action ) {
		case 'reply':
		case 'moderate-topic':
		case 'close-open-topic':
		case 'restore-post':
		case 'moderate-post':
		case 'edit-title':
		case 'edit-post':
			if ( $this->newRevision === null ) {
				throw new FailCommitException( 'Attempt to save null revision', 'fail-commit' );
			}

			$metadata = array(
				'workflow' => $this->workflow,
				'topic-title' => $this->loadTopicTitle(),
			);
			if ( !$metadata['topic-title'] ) {
				// permissions failure, should never have gotten this far
				throw new PermissionException( 'Not Allowed', 'insufficient-permission' );
			}
			if ( $this->newRevision->getPostId()->equals( $metadata['topic-title']->getPostId() ) ) {
				// When performing actions against the topic-title self::loadTopicTitle
				// returns the previous revision.
				$metadata['topic-title'] = $this->newRevision;
			}

			$this->storage->put( $this->newRevision, $metadata );
			$this->storage->put( $this->workflow, $metadata );
			$newRevision = $this->newRevision;

			// If no context was loaded render the post in isolation
			// @todo make more explicit
			try {
				$newRevision->getChildren();
			} catch ( \MWException $e ) {
				$newRevision->setChildren( array() );
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
			);

		default:
			throw new InvalidActionException( "Unknown commit action: {$this->action}", 'invalid-action' );
		}
	}

	public function renderAPI( Templating $templating, array $options ) {
		// there's probably some OO way to turn this stack of if/else into
		// something nicer. Consider better ways before extending this with
		// more conditionals
		if ( $this->action === 'history' ) {
			// single post history or full topic?
			if ( isset( $options['postId'] ) ) {
				// singular post history
				$output = $this->renderPostHistoryAPI( $templating, $options );
			} else {
				// post history for full topic
				$output = $this->renderTopicHistoryAPI( $templating, $options );
			}
		} elseif ( $this->action === 'single-view' ) {
			if ( isset( $options['revId'] ) ) {
				$revId = $options['revId'];
			} else {
				throw new InvalidInputException( 'A revision must be provided', 'invalid-input' );
			}
			$output = $this->renderSingleViewAPI( $revId );
		} elseif ( $this->action === 'close-open-topic' ) {
			// Treat topic as a post, only the post + summary are needed
			$result = $this->renderPostAPI( $templating, $options, $this->workflow->getId() );
			$topicId = $result['roots'][0];
			$revisionId = $result['posts'][$topicId][0];
			$output = $result['revisions'][$revisionId];
		} elseif ( $this->action === 'compare-post-revisions' ) {
			$output = $this->renderDiffViewAPI( $options );
		} elseif ( $this->shouldRenderTopicAPI( $options ) ) {
			// view full topic
			$output = $this->renderTopicAPI( $templating, $options );
		} else {
			// view single post, possibly specific revision
			// @todo this isn't valid for the topic title
			$output = $this->renderPostAPI( $templating, $options );
		}

		if ( $output === null ) {
			// @todo might as well throw these at the source?
			throw new PermissionException( 'Not Allowed', 'insufficient-permission' );
		}

		$output['type'] = $this->getName();
		$topic = $this->loadTopicTitle();
		$output['topicTitle'] = $templating->getContent( $topic, 'wikitext' );

		if ( $this->wasSubmitted() ) {
			// Failed actions, like reply, end up here
			$output += array(
				'submitted' => $this->submitted,
				'errors' => $this->errors,
			);
		} else {
			$output += array(
				'submitted' => $options,
				'errors' => array(),
			);
		}

		return $output;
	}

	protected function shouldRenderTopicAPI( array $options ) {
		switch( $this->action ) {
		// Any actions require rerendering the whole topic
		case 'edit-post':
		case 'moderate-post':
		case 'restore-post':
		case 'reply':
		case 'moderate-topic':
			return true;

		// View actions
		case 'view-topic':
			return true;
		case 'view-post':
			return false;
		case 'view':
			return !isset( $options['postId'] ) && !isset( $options['revId'] );
		}

		return true;
	}

	// @Todo - duplicated logic in other diff view block
	protected function renderDiffViewAPI( array $options ) {
		if ( !isset( $options['newRevision'] ) ) {
			throw new InvalidInputException( 'A revision must be provided for comparison', 'revision-comparison' );
		}
		$oldRevision = '';
		if ( isset( $options['oldRevision'] ) ) {
			$oldRevision = $options['newRevision'];
		}
		list( $new, $old ) = Container::get( 'query.post.view' )->getDiffViewResult( $options['newRevision'], $oldRevision );
		$output['revision'] = Container::get( 'formatter.revision.diff.view' )->formatApi( $new, $old, \RequestContext::getMain() );
		return $output;
	}

	// @Todo - duplicated logic in other single view block
	protected function renderSingleViewAPI( $revId ) {
		$row = Container::get( 'query.post.view' )->getSingleViewResult( $revId );
		$output['revision'] = Container::get( 'formatter.revisionview' )->formatApi( $row, \RequestContext::getMain() );
		return $output;
	}

	protected function renderTopicAPI( Templating $templating, array $options, $workflowId = '' ) {
		$serializer = Container::get( 'formatter.topic' );
		if ( !$workflowId ) {
			if ( $this->workflow->isNew() ) {
				return $serializer->buildEmptyResult( $this->workflow );
			}
			$workflowId = $this->workflow->getId();
		}

		return $serializer->formatApi(
			$this->workflow,
			Container::get( 'query.topiclist' )->getResults( array( $workflowId ) ),
			\RequestContext::getMain()
		);
	}

	/**
	 * @todo Any failed action performed against a single revisions ends up here.
	 * To generate forms with validation errors in the non-javascript renders we
	 * need to add something to this output, but not sure what yet
	 */
	protected function renderPostAPI( Templating $templating, array $options, $postId = '' ) {
		if ( $this->workflow->isNew() ) {
			throw new FlowException( 'No posts can exist for non-existent topic' );
		}

		if ( !$postId ) {
			if ( isset( $options['postId'] ) ) {
				$postId = $options['postId'];
			} elseif( $this->newRevision ) {
				// API results after a reply will have no $postId (ID is not yet
				// known when the reply is submitted) so we'll grab it from the
				// newly added revision
				$postId = $this->newRevision->getPostId();
			}
		}

		$row = Container::get( 'query.singlepost' )->getResult( UUID::create( $postId ) );
		$serializer = $this->getRevisionFormatter();
		if ( isset( $options['contentFormat'] ) ) {
			$serializer->setContentFormat( $options['contentFormat'] );
		}
		$serialized = $serializer->formatApi( $row, \RequestContext::getMain() );
		if ( !$serialized ) {
			return null;
		}

		return array(
			'roots' => array( $serialized['postId'] ),
			'posts' => array(
				$serialized['postId'] => array( $serialized['revisionId'] ),
			),
			'revisions' => array(
				$serialized['revisionId'] => $serialized,
			)
		);
	}

	protected function getRevisionFormatter() {
		$serializer = Container::get( 'formatter.revision' );
		if ( false !== array_search( $this->action, $this->requiresWikitext ) ) {
			$serializer->setContentFormat( 'wikitext' );
		}

		return $serializer;
	}

	protected function renderTopicHistoryAPI( Templating $templating, array $options ) {
		if ( $this->workflow->isNew() ) {
			throw new FlowException( 'No topic history can exist for non-existant topic' );
		}
		$found = Container::get( 'query.topic.history' )->getResults( $this->workflow->getId() );
		$serializer = $this->getRevisionFormatter();
		if ( isset( $options['contentFormat'] ) ) {
			$serializer->setContentFormat( $options['contentFormat'] );
		}
		$serializer->setIncludeHistoryProperties( true );
		$ctx = \RequestContext::getMain();

		$result = array();
		foreach ( $found as $row ) {
			$serialized = $serializer->formatApi( $row, $ctx );
			// if the user is not allowed to see this row it will return empty
			if ( $serialized ) {
				$result[$serialized['revisionId']] = $serialized;
			}
		}

		return array(
			'revisions' => $result
		);
	}

	protected function renderPostHistoryAPI( Templating $templating, array $options ) {
		throw new FlowException( 'Not implemented yet' );
	}

	/**
	 * @return PostRevision|null
	 */
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
				array( 'rev_type_id' => $this->workflow->getId() ),
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

	// The prefix used for form data$pos
	public function getName() {
		return 'topic';
	}

	protected function setNotification( $notificationType, array $extraVars = array() ) {
		$this->notification = array(
				'type' => $notificationType,
				'params' => $extraVars + array(
					'topic-workflow' => $this->workflow,
					// Use owner title rather than article title because aritlce
					// title is just Topic:UUID and we can get that from workflowid
					'title' => $this->workflow->getOwnerTitle(),
					'user' => $this->user,
				)
			);
	}
}
