<?php

namespace Flow\Block;

use Flow\Model\Workflow;
use Flow\Model\PostRevision;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Templating;
use User;

class TopicBlock extends AbstractBlock {

	protected $root;
	protected $reply;

	protected $supportedActions = array( 'reply' );

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

	// Used when the posts related to the topic are externally loaded
	static public function createWithRootPost( Workflow $workflow, ManagerGroup $storage, PostRevision $root ) {
		$obj = new self( $workflow, $storage );
		$obj->root = $root;
		return $obj;
	}

	protected function validate() {
		if ( !isset( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
		}

		if ( !isset( $this->submitted['replyTo'] ) ) {
			$this->errors['replyTo'] = wfMessage( 'flow-missing-reply-to-id' );
		} else {
			$post = $this->storage->get( 'PostRevision', $this->submitted['replyTo'] );
			if ( !$post ) {
				$this->errors['replyTo'] = wfMessage( 'flow-invalid-reply-to-id' );
			} else {
				// TODO: assert post belongs to this tree?  Does it realy matter?
				// answer: might not belong, and probably does due to inter-wiki interaction
				$this->reply = $post->reply( $this->user, $this->submitted['content'] );
			}
		}
	}

	public function commit() {
		if ( $this->action === 'reply' ) {
			if ( $this->reply === null ) {
				throw new \MWException( 'Attempt to save null reply' );
			}
			$this->storage->put( $this->reply );
		} else {
			throw new \MWException( "Unknown commit action: {$this->action}" );
		}
	}

	// The prefix used for form data
	public function getName() {
		return 'topic_list';
	}

	public function render( Templating $templating, $return = false ) {
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
		return isset( $this->submitted['replyTo'] ) ? $this->submittled['replyTo'] : null;
	}
}
