<?php

namespace Flow\Model;

use DOMElement;
use DOMNode;
use DOMText;
use User;

/**
 * Models a revisionable node within a DOMDocument
 */
class RevisionedNode {
	/**
	 * @var XmlTopic
	 */
	protected $topic;

	/**
	 * @var DOMElement
	 */
	protected $node;

	/**
	 * @var UUID
	 */
	protected $revisionId;

	/**
	 * @var UserTuple
	 */
	protected $user;

	/**
	 * @var LazyUuidCollection
	 */
	protected $revisionList;

	/**
	 * @var string
	 */
	protected $changeType;

	/**
	 * @var UUID
	 */
	protected $lastEditId;

	/**
	 * @var UserTuple
	 */
	protected $lastEditUser;

	/**
	 * @var DOMNode
	 */
	protected $content;

	/**
	 * @param XmlTopic $topic
	 * @param DOMElement $node
	 */
	public function __construct( XmlTopic $topic, DOMElement $node, $isNewNode = false ) {
		$this->topic = $topic;
		$this->node = $node;
		$this->revisionList = new LazyUuidCollection;

		$id = $node->getAttribute( 'id' );
		$this->revisionTypeId = UUID::create( $id === '' ? false : $id );

		$attr = $node->getAttribute( 'data-flow' );
		if ( $attr === '' && $isNewNode ) {
			// expected case
			return;
		} elseif ( $isNewNode ) {
			throw new \Exception( "Expected new DOMElement but data-flow is set" );
		} elseif ( $attr === '' ) {
			throw new \Exception( "data-flow attribute is empty" );
		}
		$attributes = json_decode( $attr, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( 'Failed decoding the data-flow attributes json content' );
		}
		if ( $node->childNodes->length > 1 ) {
			throw new \Exception( 'Expected single child node representing content' );
		}

		$this->revisionId = UUID::create( $attributes['revisionId'] );
		$this->revisionList->exchangeArray( $attributes['revisionList'] );
		$this->user = UserTuple::fromArray( $attributes['user'] );
		$this->changeType = $attributes['changeType'];
		$this->lastEditId = UUID::create( $attributes['lastEditId'] );
		$this->lastEditUser = UserTuple::fromArray( $attributes['lastEditUser'] );
		$this->content = $node->firstChild;
	}

	public function getNode() { return $this->node; }

	public function getRevisionId() { return $this->revisionId; }

	public function newNextRevision( UserTuple $user, DOMNode $content, $changeType ) {
		$this->newNullRevision( $user );
		$this->setContent( $content );
		$this->changeType = $changeType;
		$this->updateNode();
	}

	protected function newNullRevision( UserTuple $user ) {
		$this->revisionId = UUID::create();
		$this->revisionList[] = $this->revisionId;
		$this->user = $user;
		$this->changeType = '';
	}

	protected function setContent( DOMNode $content ) {
		$this->content = $content;
		$this->lastEditId = $this->revisionId;
		$this->lastEditUser = $this->user;
	}

	protected function updateNode( array $extra = array() ) {
		$this->node->setAttribute( 'id', $this->revisionTypeId->getAlphadecimal() );
		$this->node->setAttribute( 'data-flow-revision-id', $this->revisionId->getAlphadecimal() );
		$this->node->setAttribute(
			'data-flow',
			json_encode( array(
				'revisionId' => $this->revisionId->getAlphadecimal(),
				'user' => $this->user->toArray(),
				'revisionList' => $this->revisionList->getArrayCopy(),
				'changeType' => $this->changeType,
				'lastEditId' => $this->lastEditId->getAlphadecimal(),
				'lastEditUser' => $this->lastEditUser->toArray(),
			) + $extra )
		);

		if ( $this->content === null ) {
			$this->content = new DOMText( '' );
		}
		if ( $this->node->ownerDocument === $this->content->ownerDocument ) {
			$this->content = $this->content->cloneNode( true );
		} else {
			$this->content = $this->node->ownerDocument->importNode( $this->content, true );
		}
		if ( !$this->content instanceof DOMElement ) {
			// only DOMElement's are tags with attributes
			$container = $this->content->ownerDocument->createElement( 'p' );
			$container->appendChild( $this->content );
			$this->content = $container;
		}
		$this->content->setAttribute( 'class', 'content' );

		if ( $this->node->firstChild === null ) {
			$this->node->appendChild( $this->content );
		} else {
			$this->node->replaceChild( $this->content, $this->node->firstChild );
		}
	}
}

/**
 * Placeholder class.  Currently only identifies that
 * a particular RevisionedNode instance represents the
 * document title
 */
class TitleNode extends RevisionedNode {
}

/**
 * Model a single post within the document.
 */
class PostNode extends RevisionedNode {
	public function getPostId() {
		return $this->revisionTypeId;
	}

	public function appendChild( PostNode $child ) {
		$this->node->parentNode->appendChild( $child->node );
	}
}

/**
 * Immutable data carrier describing a mediawiki user.
 * Fields are read only, but public for convenience.
 */
class UserTuple {
	public $wiki;
	public $id;
	public $ip;

	public function __construct( $wiki, $id, $ip ) {
		$this->wiki = $wiki;
		$this->id = $id;
		$this->ip = $ip;
	}

	static public function fromArray( array $tuple ) {
		list( $wiki, $id, $ip ) = $tuple;
		return new self( $wiki, $id, $ip );
	}

	static public function fromUser( User $user ) {
		return new self(
			wfWikiId(),
			$user->getId(),
			$user->isAnon() ? $user->getName() : ''
		);
	}

	public function toArray() {
		return array(
			$this->wiki,
			$this->id,
			$this->ip
		);
	}
}

/**
 * Accept a list of uuids, only initialize them into UUID objects
 * as required
 */
class LazyUuidCollection extends \ArrayObject {
	protected $initialized = array();

	public function __construct( array $values = array() ) {
		parent::__construct( array_values( $values ) );
	}

	public function exchangeArray( $replacement ) {
		$this->initialized = array();
		return parent::exchangeArray( $replacement );
	}

	public function offsetGet( $index ) {
		if ( !array_key_exists( $index, $this->initialized ) ) {
			$this->initialized[$index] = UUID::create( parent::offsetGet( $index ) );
		}
		return $this->initialized[$index];
	}

	public function offsetSet( $index, $value ) {
		if ( !$value instanceof UUID ) {
			throw new \Exception( 'Can only add uuids' );
		}
		if ( $index !== null ) {
			throw new \Exception( 'Can only append uuids' );
		}
		$this->initialized[$this->count()] = $value;
		parent::offsetSet( null, $value->getAlphadecimal() );
	}
}
