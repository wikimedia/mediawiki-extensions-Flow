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
use Flow\Templating;
use EchoEvent;
use User;

class PostBlock extends AbstractBlock {

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
		'hide-post', 'delete-post', 'censor-post', 'restore-post',
	);

	public function __construct( Workflow $workflow, ManagerGroup $storage, NotificationController $notificationController, $revision ) {
		parent::__construct( $workflow, $storage, $notificationController );
		if ( $revision instanceof PostRevision ) {
			$this->root = $revision;
		} elseif ( $revision instanceof RootPostLoader ) {
			$this->rootLoader = $revision;
		} else {
			throw new \InvalidArgumentException(
				'Expected PostRevision or RootPostLoader, received: ' . is_object( $revision ) ? get_class( $revision ) : gettype( $revision )
			);
		}
	}

	protected function validate() {
		switch( $this->action ) {
			case 'reply':
				$this->validateReply();
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

	protected function validateModeratePost( $moderationState ) {
		if ( empty( $this->submitted['id'] ) ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['id'] );
		if ( !$post ) {
			$this->errors['moderate-post'] = wfMessage( 'flow-error-invalid-postId' );
			return;
		}

		$this->newRevision = $post->moderate( $this->user, $moderationState );
		if ( !$this->newRevision ) {
			$this->errors['moderate'] = wfMessage( 'flow-error-not-allowed' );
		}
	}

	protected function validateRestorePost() {
		if ( empty( $this->submitted['id'] ) ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-missing-postId' );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['id'] );
		if ( !$post ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-invalid-postId' );
			return;
		}

		$this->newRevision = $post->restore( $this->user );
		if ( !$this->newRevision ) {
			$this->errors['restore-post'] = wfMessage( 'flow-error-not-allowed' );
		}
	}

	protected function validateEditPost() {
		if ( empty( $this->submitted['id'] ) ) {
			$this->errors['edit-post'] = wfMessage( 'flow-no-post-provided' );
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
			return;
		}
		$post = $this->loadRequestedPost( $this->submitted['id'] );
		if ( !$post ) {
			$this->errors['edit-post'] = wfMessage( 'flow-post-not-found' );
			return;
		}
		if ( !$post->isAllowedToEdit( $this->user ) ) {
			$this->errors['edit-post'] = wfMessage( 'flow-error-edit-restricted' );
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

				$renderFunction = function( $templating ) use ( $self, $newRevision, $rootPost ) {
					// @todo: build PostBlock object from $newRevision, then call ->render() on it
					return $self->render( $templating, array(), true );
				};

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
		$templating->getOutput()->addModules( 'ext.flow.discussion' );
		switch( $this->action ) {
			case 'post-history':
				return $this->renderPostHistory( $templating, $options, $return );

			case 'topic-history':
				return $templating->render( "flow:topic-history.html.php", array(
					'block' => $this,
					'topic' => $this->workflow,
					'history' => $this->loadTopicHistory(),
				) );

			case 'edit-post':
				return $this->renderEditPost( $templating, $options, $return );

			default:
				$root = $this->loadRootPost();

				// @todo: check for specific post in $options['id']?

				global $wgUser, $wgFlowTokenSalt;

				return $this->render(
					'flow:post.html.php',
					array(
						'block' => $this,
						'post' => $root,
						// An ideal world may pull this from the container, but for now this is fine.  This templating
						// class has too many responsibilities to keep receiving all required objects in the constructor.
						'postActionMenu' => new PostActionMenu(
							$this->urlGenerator,
							$wgUser,
							$this,
							$root,
							$wgUser->getEditToken( $wgFlowTokenSalt )
						),
					),
					$return
				);
		}
	}

	protected function renderPostHistory( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['id'] ) ) {
			throw new \Exception( 'No postId provided' );
		}
		return $templating->render( "flow:post-history.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'history' => $this->getHistory( $options['id'] ),
		), $return );
	}

	protected function renderEditPost( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['id'] ) ) {
			throw new \Exception( 'No postId provided' );
		}
		$post = $this->loadRequestedPost( $options['id'] );
		if ( $post->isModerated() ) {
			throw new \Exception( 'Cannot edit restricted post.  Restore first.' );
		}
		return $templating->render( "flow:edit-post.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'post' => $this->loadRequestedPost( $options['id'] ),
		), $return );
	}

	public function renderAPI( Templating $templating, array $options ) {
		if ( isset( $options['id'] ) ) {
			$rootPost = $this->loadRootPost();
			$post = $rootPost->findDescendant( $options['id'] );

			if ( ! $post ) {
				throw new MWException( "Requested post could not be found" );
			}

			return array( $this->renderPostAPI( $templating, $post, $options ) );
		} else {
			return $this->renderTopicAPI( $templating, $options );
		}
	}

	protected function renderPostAPI( Templating $templating, PostRevision $post, array $options ) {
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
			$output['user'] = $post->getUserText();
		}

		if ( ! isset( $options['no-children'] ) ) {
			$children = array( '_element' => 'post' );

			foreach( $post->getChildren() as $child ) {
				$children[] = $this->renderPostAPI( $templating, $child, $options );
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
			$output[] = array(
				'revision-id' => $revision->getRevisionId()->getHex(),
				'revision-author' => $revision->getUserText(),
				'revision-change-type' => $revision->getChangeType(),
			);
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
		return 'post';
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
