<?php

namespace Flow\Block;

use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\PostRevision;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Templating;
use User;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $topicTitle;
	protected $rootLoader;
	protected $newRevision;

	protected $supportedActions = array( 'edit-title', 'reply', 'delete-topic', 'delete-post', 'restore-post' );

	public function __construct( Workflow $workflow, ManagerGroup $storage, $root ) {
		parent::__construct( $workflow, $storage );
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

		case 'reply':
			$this->validateReply();
			break;

		case 'delete-topic':
			// this should be a workflow level action, not implemented per-block
			$this->validateDeleteTopic();
			break;

		case 'delete-post':
			$this->validateDeletePost();
			break;

		case 'restore-post':
			$this->validateRestorePost();
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

			$this->topicTitle = $topicTitle->newNextRevision( $this->user, $this->submitted['content'] );
		}
	}

	protected function validateReply() {
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
		} else {
			$this->parsedContent = $this->convertWikitextToHtml5( $this->submitted['content'] );
			if ( empty( $this->parsedContent ) ) {
				$this->errors['content'] = wfMessage( 'flow-empty-parsoid-result' );
			}
		}

		if ( !isset( $this->submitted['replyTo'] ) ) {
			$this->errors['replyTo'] = wfMessage( 'flow-missing-reply-to-id' );
		} else {
			$this->submitted['replyTo'] = UUID::create( $this->submitted['replyTo']  );
			$post = $this->storage->get( 'PostRevision', $this->submitted['replyTo'] );
			if ( !$post ) {
				$this->errors['replyTo'] = wfMessage( 'flow-invalid-reply-to-id' );
			} else {
				// TODO: assert post belongs to this tree?  Does it realy matter?
				// answer: might not belong, and probably does matter due to inter-wiki interaction
				$this->newRevision = $post->reply( $this->user, $this->parsedContent, 'flow-comment-added' );
			}
		}
	}

	protected function validateDeleteTopic() {
		if ( !$this->workflow->lock( $this->user ) ) {
			$this->errors['delete-topic'] = wfMessage( 'flow-delete-topic-failed' );
		}
	}

	protected function validateDeletePost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['delete-post'] = wfMessage( 'flow-no-post-provided' );
			return;
		}
		$found = $this->storage->find(
			'PostRevision',
			array( 'tree_rev_descendant_id' => UUID::create( $this->submitted['postId'] ) ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		if ( !$found ) {
			$this->errors['delete-post'] = wfMessage( 'flow-post-not-found' );
			return;
		}
		// TODO: validate it has $this->workflow as its topic
		$post = reset( $found );

		// returns new revision to save
		$this->newRevision = $post->addFlag( $this->user, 'deleted', 'flow-comment-deleted' );
		if ( !$this->newRevision ) {
			$this->errors['delete-post'] = wfMessage( 'flow-delete-post-failed' );
		}
	}

	protected function validateRestorePost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['restore-post'] = wfMessage( 'flow-no-post-provided' );
			return;
		}
		$found = $this->storage->find(
			'PostRevision',
			array( 'tree_rev_descendant_id' => UUID::create( $this->submitted['postId'] ) ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		if ( !$found ) {
			$this->errors['restore-post'] = wfMessage( 'flow-post-not-found' );
			return;
		}
		$post = reset( $found );

		$this->newRevision = $post->removeFlag( $this->user, 'deleted', 'flow-comment-restored' );
		if ( !$this->newRevision ) {
			$this->errors['restore-post'] = wfMessage( 'flow-post-restore-failed' );
		}
	}

	// @todo: I assume not only topic reply, but also TopicListBlock & SummaryBlock's content need to be converted?
	protected function convertWikitextToHtml5( $wikitext ) {
		global $wgFlowUseParsoid;

		if ( $wgFlowUseParsoid ) {
			global $wgFlowParsoidURL, $wgFlowParsoidPrefix, $wgFlowParsoidTimeout;

			$parsoidOutput = \Http::post(
				$wgFlowParsoidURL . '/' . $wgFlowParsoidPrefix . '/',
				array(
					'postData' => array(
						'content' => $wikitext,
						'format' => 'html',
					),
					'timeout' => $wgFlowParsoidTimeout
				)
			);

			// Strip out the Parsoid boilerplate
			$dom = new \DOMDocument();
			$dom->loadHTML( $parsoidOutput );
			$body = $dom->getElementsByTagName( 'body' )->item(0);
			$html = '';

			foreach( $body->childNodes as $child ) {
				$html .= $child->ownerDocument->saveXML( $child );
			}

			return $html;
		} else {
			global $wgParser;

			$title = \Title::newFromText( 'Flow', NS_SPECIAL );

			$options = new \ParserOptions;
			$options->setTidy( true );

			$output = $wgParser->parse( $wikitext, $title, $options );
			return $output->getText();
		}
	}

	public function commit() {
		switch( $this->action ) {
		case 'reply':
		case 'delete-post':
		case 'restore-post':
			if ( $this->newRevision === null ) {
				throw new \MWException( 'Attempt to save null revision' );
			}
			$this->storage->put( $this->newRevision );
			break;

		case 'edit-title':
			$this->storage->put( $this->topicTitle );
			break;

		case 'delete-topic':
			$this->storage->put( $this->workflow );
			break;

		default:
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		if ( $this->action === 'post-history' ) {
			if ( empty( $options['postId'] ) ) {
				var_dump( $this->getName() );
				var_dump( $options );
				throw new \Exception( 'No postId specified' );
				$history = array();
			} else {
				$history = $this->getHistory( $options['postId'] );
			}
			return $templating->render( "flow:post-history.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'history' => $history,
			), $return );
		} elseif ( $this->action === 'edit-title' ) {
			return $templating->render( "flow:edit-title.html.php", array(
				'block' => $this,
				'user' => $this->user,
				'topic' => $this->workflow,
				'topicTitle' => $this->loadTopicTitle(),
			) );
		}

		$templating->getOutput()->addModules( 'ext.flow.base' );

		$root = $this->loadRootPost();
		if ( isset( $options['postId'] ) ) {
			$stack = array( $root );
			$found = false;
			while( $stack ) {
				$post = array_pop( $stack );
				if ( $post->getPostId()->getHex() === $options['postId'] ) {
					$found = true;
					break;
				}
				foreach ( $post->getChildren() as $child ) {
					$stack[] = $child;
				}
			}
			if ( $found === false ) {
				throw new \Exception( 'Requested postId is not available within post tree' );
			}
			$root = $post;
		}
		return $templating->render( "flow:topic.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'root' => $root,
			'user' => $this->user,
		), $return );
	}

	public function renderAPI ( array $options ) {
		$output = array();
		$rootPost = $this->loadRootPost();
		$topic = $this->workflow;

		$output = array(
			'_element' => 'post',
			'title' => $rootPost->getContent(),
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

		foreach( $rootPost->getChildren() as $child ) {
			$output[] = $this->renderPostAPI( $child, $options );
		}

		return $output;
	}

	protected function renderPostAPI( PostRevision $post, array $options ) {
		$output = array();

		$output['post-id'] = $post->getPostId()->getHex();

		if ( $post->isFlagged( 'deleted' ) ) {
			$output['post-deleted'] = 'post-deleted';
		} else {
			$output['content'] = array( '*' => $post->getContent() );
			$output['user'] = $post->getUserText();
		}

		$children = array( '_element' => 'post' );

		foreach( $post->getChildren() as $child ) {
			$children[] = $this->renderPostAPI( $child, $options );
		}

		if ( count($children) > 1 ) {
			$output['replies'] = $children;
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
				'revision-comment' => $revision->getComment(),
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

		if ( count($searchConditions) === 0 ) {
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

}
