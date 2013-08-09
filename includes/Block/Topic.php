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

	protected $supportedActions = array( 'reply', 'delete-topic', 'delete-post', 'restore-post' );

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
				throw new \Exception( 'Could not locate post' );
				$history = array();
			} else {
				$history = $this->storage->find(
					'PostRevision',
					array( 'tree_rev_descendant_id' => UUID::create( $options['postId'] ) ),
					array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
				);
			}
			return $templating->render( "flow:post-history.html.php", array(
				'block' => $this,
				'topic' => $this->workflow,
				'history' => $history,
			), $return );
		}

		$templating->getOutput()->addModules( 'ext.flow.base' );

		return $templating->render( "flow:topic.html.php", array(
			'block' => $this,
			'topic' => $this->workflow,
			'root' => $this->loadRootPost(),
		), $return );
	}

	protected function loadRootPost() {
		if ( $this->root !== null ) {
			return $this->root;
		}
		return $this->root = $this->rootLoader->get( $this->workflow->getId() );
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
		return 'topic_list';
	}

}
