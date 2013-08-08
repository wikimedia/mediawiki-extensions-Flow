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
	protected $rootLoader;
	protected $newRevision;
	protected $requestedPost;

	protected $supportedActions = array(
		'delete-post', 'restore-post', 'edit-post',
		'reply', 'delete-topic',
	);

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

		case 'edit-post':
			$this->validateEditPost();
			break;

		default:
			throw new \MWException( "Unexpected action: {$this->action}" );
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

	protected function validateEditPost() {
		if ( empty( $this->submitted['postId'] ) ) {
			$this->errors['edit-post'] = wfMessage( 'flow-no-post-provided' );
			return;
		}
		if ( empty( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
		} else {
			$this->parsedContent = $this->convertWikitextToHtml5( $this->submitted['content'] );
			if ( empty( $this->parsedContent ) ) {
				$this->errors['content'] = wfMessage( 'flow-empty-parsoid-result' );
				return;
			}
		}
		$post = $this->loadRequestedPost( $this->submitted['postId'] );
		if ( $post ) {
			$this->newRevision = $post->newNextRevision( $this->user, $this->parsedContent );
		} else {
			$this->errors['edit-post'] = wfMessage( 'flow-post-not-found' );
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
		case 'edit-post':
			if ( $this->newRevision === null ) {
				throw new \MWException( 'Attempt to save null revision' );
			}
			$this->storage->put( $this->newRevision );
			break;

		case 'delete-topic':
			$this->storage->put( $this->workflow );
			break;

		default:
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	public function render( Templating $templating, array $options, $return = false ) {
		$templating->getOutput()->addModules( 'ext.flow.base' );
		switch( $this->action ) {
		case 'post-history':
			return $this->renderPostHistory( $templating, $options, $return );

		case 'edit-post':
			return $this->renderEditPost( $templating, $options, $return );

		default:
			return $this->renderGeneric( $templating, $options, $return );
		}
	}

	protected function renderPostHistory( Templating $templating, array $options, $return = false ) {
		if ( empty( $options['postId'] ) ) {
			var_dump( $this->getName() );
			var_dump( $options );
			throw new \Exception( 'Could not locate post' );
			$history = array();
		} else {
			$history = $this->getHistory( $options['postId'] );
		}
		return $templating->render( "flow:post-history.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'history' => $history,
		), $return );
	}

	protected function renderEditPost( Templating $templating, array $options, $return = false ) {
		if ( !isset( $options['postId'] ) ) {
			throw new \Exception( 'No postId provided' );
		}
		return $templating->render( "flow:edit-post.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'post' => $this->loadRequestedPost( $options['postId'] ),
		), $return );
	}

	protected function renderGeneric( Templating $templating, array $options, $return = false ) {
		return $templating->render( "flow:topic.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'root' => $this->loadRootPost(),
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
		return $this->root = $this->rootLoader->get( $this->workflow->getId() );
	}

	protected function loadRequestedPost( $postId ) {
		if ( !isset( $this->requestedPost[$postId] ) ) {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => $postId ),
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

}
