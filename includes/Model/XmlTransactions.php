<?php

namespace Flow\Model;

use Flow\RevisionActionPermissions;
use DOMDocument;
use DOMElement;
use DOMText;
use DOMXPath;
use Status;

/**
 * Transaction classes implement a single revisioned change
 * to a DOMDocument.
 *
 * @todo better name
 */
interface XmlTopicTransaction {

	/**
	 * Apply the specified change to the topic
	 *
	 * @param XmlTopic $topic
	 * @param DOMDocument $dom
	 */
	function apply( XmlTopic $topic, DOMDocument $dom );

	/**
	 * @param XmlTopic $topic
	 * @param RevisionActionPermissions $permissions
	 * @return Status isGood if the transaction can be made against this topic.
	 *  The value when good is the transaction itself.
	 */
	function validate( XmlTopic $topic,  RevisionActionPermissions $permissions );
}

class XmlTopicCreateTitle implements XmlTopicTransaction {
	public function __construct( UserTuple $user, DOMText $content, UUID $revisionId = null ) {
		$this->user = $user;
		$this->content = $content;
	}

	public function apply( XmlTopic $topic, DOMDocument $dom ) {
		$topic->getTitle()->newNextRevision( $this->user, $this->content, 'edit-title' );
	}

	public function validate( XmlTopic $topic,  RevisionActionPermissions $permissions ) {
		if ( $topic->getTitle()->getRevisionId() !== null ) {
			return Status::newFatal( 'flow-edit-conflict' );
		}
		return Status::newGood();
	}
}
/**
 * Edit the document's title.  The provided revisionId must match
 * the revision id of the title node in the document.
 */
class XmlTopicEditTitle implements XmlTopicTransaction {
	/**
	 * @param UserTuple $tuple
	 * @param DOMText $content
	 * @param UUID|null $revisionId Id the user believes to be most recent or
	 *  null when no title exists.
	 */
	public function __construct( UserTuple $user, DOMText $content, UUID $revisionId = null ) {
		$this->user = $user;
		$this->content = $content;
		$this->revisionId = $revisionId;
	}

	public function apply( XmlTopic $topic, DOMDocument $dom ) {
		$topic->getRevision( $this->revisionId )
			->newNextRevision( $this->user, $this->content, 'edit-title' );
	}

	public function validate( XmlTopic $topic,  RevisionActionPermissions $permissions ) {

		$title = $topic->getRevision( $this->revisionId );
		if ( !$title ) {
			return Status::newFatal( 'flow-edit-conflict' );
		}
		/* @todo $user->equals( $other );
		if ( !$permissions->isAllowed( $title, 'edit-title' ) ) {
			return Status::newFatal( 'flow-error-permissions' );
		}
		*/
		return Status::newGood( $this );
	}
}

/**
 * Edit a post. The provided revision id must match the revision id
 * in the document.
 */
class XmlTopicEditPost implements XmlTopicTransaction {
	protected $user;
	protected $content;
	protected $revisionId;

	public function __construct( UserTuple $user, DOMElement $content, UUID $revisionId ) {
		$this->user = $user;
		$this->content = $content;
		$this->revisionId = $revisionId;
	}

	public function apply( XmlTopic $topic, DOMDocument $dom ) {
		$topic->getRevision( $this->revisionId )
			->newNextRevision( $this->user, $this->content, 'edit' );
	}

	public function validate( XmlTopic $topic, RevisionActionPermissions $permissions ) {
		/*
		if ( !$permissions->getUser()->equals( $user ) ) {
			throw new RuntimeException( 'wrong user' );
		}
		*/
		$post = $topic->getRevision( $this->revisionId );
		if ( !$post ) {
			// probably edit conflict, the revision is no longer in the document
			return Status::newFatal( 'flow-error-otherthing' );
		}
		/*
		if ( !$permissions->isAllowed( $this, 'edit-post' ) ) {
			return Status::newFatal( 'flow-error-permissions' );
		}
		*/
		return Status::newGood( $this );
	}
}

/**
 * Create a top level reply to the topic.
 */
class XmlTopicCreateReply implements XmlTopicTransaction {
	protected $user;
	protected $content;
	protected $revisionId;

	public function __construct( UserTuple $user, DOMElement $content ) {
		$this->user = $user;
		$this->content = $content;
	}

	public function apply( XmlTopic $topic, DOMDocument $dom ) {
		$node = $dom->createElement( 'div' );
		$this->post = new PostNode( $topic, $node, /* $isNewNode = */ true );
		$this->post->newNextRevision( $this->user, $this->content, 'reply' );
		$topic->getReplies()->appendChild( $node );
	}

	public function getPost() {
		return $this->post;
	}

	public function validate( XmlTopic $topic,  RevisionActionPermissions $permissions ) {
		/*
		if ( !$permissions->isAllowed( null, 'reply' ) ) {
			return Status::newFatal( 'flow-error-permissions' );
		}
		*/
		return Status::newGood( $this );
	}
}

/**
 * Create a reply to an existing reply within the document
 */
class XmlTopicNestedReply implements XmlTopicTransaction {
	public function __construct( UserTuple $user, DOMElement $content, UUID $postId ) {
		$this->user = $user;
		$this->content = $content;
		$this->postId = $postId;
	}

	public function apply( XmlTopic $topic, DOMDocument $dom ) {
		$node = $dom->createElement( 'div' );
		$this->post = new PostNode( $topic, $node, /* $isNewNode = */ true );
		$this->post->newNextRevision( $this->user, $this->content, 'reply' );
		$topic->getPost( $this->postId )
			->getNode()
			->appendChild( $node );

	}

	public function validate( XmlTopic $topic,  RevisionActionPermissions $permissions ) {
		$post = $topic->getPost( $this->postId );
		if ( !$post ) {
			return Status::newFatal( 'flow-error-otherthing' );
		}
		/*
		if ( !$permissions->isAllowed( $post, 'reply' ) ) {
			return Status::newFatal( 'flow-error-permissions' );
		}
		*/
		return Status::newGood( $this );
	}
}

