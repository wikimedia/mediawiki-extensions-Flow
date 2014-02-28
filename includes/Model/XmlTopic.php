<?php

namespace Flow\Model;

use Flow\ParsoidUtils;
use Flow\RevisionActionPermissions;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Status;
use User;

class XmlTopic {

	/**
	 * @var DOMDocument|null
	 */
	protected $dom;

	/**
	 * To facilitate the requirement of accepting new xml and attempting to merge
	 * again when an unrelated portion of the document changes we maintain a list
	 * of transformations that need to be applied to the document.
	 *
	 * @var XmlTopicTransaction[]
	 */
	protected $commands = array();

	/**
	 * @var TitleNode|null
	 */
	protected $title;

	/**
	 * @var PostNode[] Indexed by alphadecimal post id
	 */
	protected $posts = array();

	/**
	 * @var PostNode[] Indexed by alphadecimal revision id
	 */
	protected $revisions = array();

	public function __construct( RevisionActionPermissions $permissions, $html = null ) {
		$this->permissions = $permissions;
		if ( $html === null ) {
			$html = $this->initialHtml();
		}
		$this->setHtml( $html );
	}

	public function __clone() {
		if ( $this->dom ) {
			$this->setHtml( $this->getHtml() );
		}
	}

	public function query( $expression, DOMNode $contextNode = null ) {
		return $this->xpath->query( $expression, $contextNode );
	}

	public function setHtml( $html ) {
		$dom = ParsoidUtils::createDom( $html );
		$dom->preserveWhitespace = false;
		$dom->formatOutput = true;
		if ( $this->dom !== null ) {
			// @todo html must be the same topic
		}
		$this->dom = $dom;
		$this->xpath = new DOMXPath( $dom );
		$this->title = null;
		$this->replies = null;
		$this->posts = array();
		$this->revisions = array();
	}

	/**
	 * @return string Html
	 */
	public function getHtml() {
		return $this->dom->saveHTML();
	}

	/**
	 * @return TitleNode
	 */
	public function getTitle() {
		if ( $this->title === null ) {
			$nodeList = $this->dom->getElementsByTagName( 'title' );
			if ( $nodeList->length > 0 ) {
				$node = $nodeList->item( 0 );
				$isNewNode = $node->getAttribute( 'data-flow' ) === '';
				$this->title = new TitleNode( $this, $node, $isNewNode );
			} else {
				$nodeList = $this->dom->getElementsByTagName( 'head' );
				if ( $nodeList->length === 0 ) {
					throw new \Exception( 'Document has no head tag' );
				}

				$node = $this->dom->createElement( 'title' );
				$nodeList->item( 0 )->appendChild( $node );
				$this->title = new TitleNode( $this, $node, /* $isNewNode = */ true );
			}
		}
		return $this->title;

	}

	/**
	 * @return DOMElement
	 * @todo wrap in a model class?
	 */
	public function getReplies() {
		if ( $this->replies ) {
			return $this->replies;
		}

		$this->replies = $this->dom->getElementById( 'replies' );
		if ( $this->replies !== null ) {
			return $this->replies;
		}

		$this->replies = $this->dom->createElement( 'div' );
		$this->replies->setAttribute( 'id', 'replies' );
		$this->dom->getElementsByTagName( 'body' )
			->item( 0 )
			->appendChild( $this->replies );

		return $this->replies;
	}

	/**
	 * @param UUID $id
	 * @return PostNode
	 */
	public function getPost( UUID $id ) {
		$alpha = $id->getAlphadecimal();
		if ( !isset( $this->posts[$alpha] ) ) {
			$node = $this->dom->getElementById( $alpha );
			if ( $node === null ) {
				throw new \Exception( "Non-existant post node: $alpha" );
			}
			$this->posts[$alpha] = $post = new PostNode( $this, $node );
			$this->revisions[$post->getRevisionId()->getAlphadecimal()] = $post;
		}
		return $this->posts[$alpha];
	}

	/**
	 * @param UUID $id
	 * @return PostNode
	 */
	public function getRevision( UUID $id ) {
		$alpha = $id->getAlphadecimal();
		if ( !isset( $this->revisions[$alpha] ) ) {
			$nodeList = $this->query( "//div[@data-flow-revision-id='$alpha']" );
			if ( $nodeList->length === 0 ) {
				return $this->revisions[$alpha] = null;
			}
			if ( $nodeList->length > 1 ) {
				throw new \Exception( 'Found multiple nodes for single revision' );
			}
			$this->revisions[$alpha] = $post = new PostNode( $this, $nodeList->item( 0 ) );
			$this->posts[$post->getPostId()->getAlphadecimal()] = $post;
		}
		return $this->revisions[$alpha];
	}

	protected function addCommand( XmlTopicTransaction $command ) {
		$status = $command->validate( $this, $this->permissions );
		if ( $status->isGood() ) {
			$this->commands[] = $command;
		}
		return $status;
	}

	/**
	 * @param User $user
	 * @param string $content
	 * @param UUID|null $revisionId The revision the user believes to be most recent
	 *  or null if the title is not yet revisioned
	 * @return Status On success value is the created command
	 */
	public function editTitle( User $user, $content, UUID $revisionId = null ) {
		if ( $revisionId === null ) {
			$command = new XmlTopicCreateTitle(
				UserTuple::fromUser( $user ),
				new DOMText( $content )
			);
		} else {
			$command = new XmlTopicEditTitle(
				UserTuple::fromUser( $user ),
				new DOMText( $content ),
				$revisionId
			);
		}
		return $this->addCommand( $command );
	}

	/**
	 * @param User $user
	 * @param DOMElement $content
	 * @param UUID $revisionId
	 * @return Status On success value is the created command
	 */
	public function edit( User $user, DOMElement $content, UUID $revisionId ) {
		return $this->addCommand( new XmlTopicEditPost(
			UserTuple::fromUser( $user ),
			$content,
			$revisionId
		) );
	}

	/**
	 * @param User $user
	 * @param DOMElement $content
	 * @param UUID $postId
	 * @return Status On success value is the created command
	 */
	public function reply( User $user, DOMElement $content, UUID $postId = null ) {
		if ( $postId ) {
			$command = new XmlTopicNestedReply(
				UserTuple::fromUser( $user ),
				$content,
				$postId
			);
		} else {
			$command = new XmlTopicCreateReply(
				UserTuple::fromUser( $user ),
				$content
			);
		}

		return $this->addCommand( $command );
	}

	/**
	 * @return XmlTopic
	 */
	public function createNextRevision() {
		$topic = clone $this;
		foreach ( $topic->commands as $command ) {
			$command->apply( $topic, $topic->dom );
		}
		$topic->commands = array();

		return $topic;
	}

	/**
	 * @return DOMDocument
	 */
	public function getDocument() {
		return $this->dom;
	}

	/**
	 * @return string HTML
	 */
	protected function initialHtml() {
		return <<<HTML
<!DOCTYPE html>
<html>
	<head></head>
	<body></body>
</html>
HTML;
	}
}
