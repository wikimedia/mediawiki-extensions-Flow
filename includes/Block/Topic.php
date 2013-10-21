<?php

namespace Flow\Block;

use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\NotificationController;
use Flow\Templating;
use User;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $topicTitle;
	protected $rootLoader;
	protected $newRevision;
	protected $notification;
	protected $requestedPost;

	protected $post;

	// POST actions, GET do not need to be listed
	// unrecognized GET actions fallback to 'view'
	protected $supportedActions = array(
		'hide-topic', 'edit-title',
	);

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

	protected function validate() {
		switch( $this->action ) {
		case 'edit-title':
			$this->validateEditTitle();
			break;

		case 'hide-topic':
			// this should be a workflow level action, not implemented per-block
			$this->validateHideTopic();
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

	protected function validateHideTopic() {
		if ( !$this->workflow->lock( $this->user ) ) {
			$this->errors['hide-topic'] = wfMessage( 'flow-error-hide-failure' );
		}
	}

	public function commit() {
		$this->workflow->updateLastModified();

		switch( $this->action ) {
		case 'edit-title':
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
			}

			if ( is_array( $this->notification ) ) {
				$this->notification['params']['revision'] = $this->newRevision;

				$this->notificationController->notifyPostChange( $this->notification['type'], $this->notification['params'] );
			}

			return array(
				'new-revision-id' => $this->newRevision->getRevisionId(),
				'render-function' => $renderFunction,
			);

		case 'delete-topic': // @todo: what? this one doesn't even exist ^^
			$this->storage->put( $this->workflow );

			return 'success';

		// @todo: hide-topic (and other moderations actions) are missing

		default:
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModules( 'ext.flow.discussion' );
		switch( $this->action ) {
		case 'topic-history':
			return $templating->render( "flow:topic-history.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'history' => $this->loadTopicHistory(),
			) );

		case 'edit-title':
			return $templating->render( "flow:edit-title.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'topicTitle' => $this->loadTopicTitle(),
			) );

		default:
			$root = $this->loadRootPost();

			return $templating->render( "flow:topic.html.php", array(
				'block' => $this,
				'topic' => $this->getWorkflow(),
				'root' => $root
			), $return );
		}
	}

	public function renderAPI( Templating $templating, array $options ) {
		return $this->renderTopicAPI( $templating, $options );
	}

	public function renderTopicAPI( Templating $templating, array $options ) {
		$rootPost = $this->loadRootPost();
		$topic = $this->workflow;

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
			$output['rendered'] = $this->render( $templating, array(), true );
		}

		foreach( $rootPost->getChildren() as $child ) {
			$output[] = $this->renderPostAPI( $templating, $child, $options );
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

	/**
	 * Returns an array of all PostBlock children for this topic.
	 *
	 * @return array
	 */
	public function getPosts() {
		$root = $this->loadRootPost();
		$revisions = $root->getChildren();

		$posts = array();

		foreach ( $revisions as $revision ) {
			$post = $this->getPostBlock( $revision );
			$hexId = $post->getWorkflowId()->getHex();
			$posts[$hexId] = $post;
		}

		return $posts;
	}

	/**
	 * Returns PostBlock object for the given PostRevision.
	 *
	 * @param PostRevision $revision
	 * @return PostBlock
	 */
	public function getPostBlock( PostRevision $revision ) {
		$workflow = $this->findWorkflow( 'post_definition_id' );

		$post = new PostBlock( $workflow, $this->storage, $this->notificationController, $revision );
		$post->init( $this->action, $this->user );

		return $post;
	}
}
