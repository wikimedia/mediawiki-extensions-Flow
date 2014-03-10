<?php

namespace Flow;

use Flow\Model\PostRevision;
use ArrayObject;
use Closure;
use DOMDocument;
use DOMNode;
use LinkBatch;
use Linker;
use Title;
use FormatJson;

/**
 * Parsoid ignores red links. With good reason: redlinks should only be
 * applied when rendering the content, not when it's created. This
 * class updates HTML content from parsoid with anchors generated by
 * Linker::link.
 *
 * An optional first stage, collectLinks, allows batching together the
 * db lookup for red vs blue status.  It receives an html string and
 * finds all anchor's which contain parsoid Title strings and loads
 * those Title's into a LinkBatch. Additionally the register method
 * applies collectLinks to a post and its children recursively.
 *
 * The second stage, apply, receives an html string. It find all
 * anchor's which contain parsoid Title strings and replaces them with
 * Linker::link generated anchors.  Current attributes and anchor
 * content are passed into Linker::link.
 *
 * Usage:
 *
 *	$redlinker = new Redlinker( Title::newMainPage(), new LinkBatch );
 *
 *	// Collect links to batch from html content directly
 *	foreach ( $foos as $foo ) {
 *	    $redlinker->collectLinks( $foo->getContent() );
 *	}
 *
 *	// Alternatively, the register method will read the content of a
 *	// post and all its children recursively.
 *	$redlinker->registerPost( $topicPost );
 *
 *	// Before outputing content
 *	$content = $redlinker->apply( $foo->getContent() );
 */
class Redlinker {

	/**
	 * @var Title To resolve relative links against
	 */
	protected $title;

	/**
	 * @var LinkBatch
	 */
	protected $batch;

	/**
	 * @var PostRevision[] Map from recursion identifier to related revision
	 */
	protected $identifiers = array();

	/**
	 * @var boolean[] Array of registered post Ids in the key, always boolean true value
	 */
	protected $registered = array();

	/**
	 * @var ArrayObject Array of processed post ids. Uses object to simplify
	 *                  use passing into closures.
	 */
	protected $processed;

	/**
	 * @var Closure Callback used for post recursion
	 */
	protected $callback;

	/**
	 * @param Title $title To resolve relative links against
	 * @param LinkBatch $batch
	 */
	public function __construct( Title $title, LinkBatch $batch ) {
		$this->title = $title;
		$this->batch = $batch;
		$this->processed = new ArrayObject;
	}

	/**
	 * Registers callback function to find content links in Parsoid html.
	 * The goal is to batch-load and add to LinkCache as much links as possible.
	 *
	 * This can be registered on multiple posts (e.g. multiple topics) to
	 * batch-load as much as possible; all of the identifiers have to be
	 * saved and will be processed as soon as they first are needed.
	 */
	public function registerPost( PostRevision $post ) {
		$revisionId = $post->getRevisionId()->getAlphadecimal();
		if ( !isset( $this->processed[$revisionId], $this->registered[$revisionId] ) ) {
			$this->registered[$revisionId] = true;
			$identifier = $post->registerRecursive( $this->recursiveCallback(), array(), __METHOD__ );
			$this->identifiers[$identifier] = $post;
		}
	}

	/**
	 * @return Closure Callback for recursing through PostRevision instances
	 */
	protected function recursiveCallback() {
		if ( $this->callback !== null ) {
			return $this->callback;
		}

		$processed = $this->processed;
		$self = $this;
		return $this->callback = function( PostRevision $post, $result ) use ( $self, $processed ) {
			// topic titles don't contain html
			if ( $post->isTopicTitle() ) {
				return array( array(), true );
			}

			// make sure a post is not checked more than once
			$revisionId = $post->getRevisionId()->getAlphadecimal();
			if ( isset( $processed[$revisionId] ) ) {
				return array( array(), false );
			}
			$processed[$revisionId] = true;

			$self->collectLinks( $post->getContent( 'html' ) );

			/*
			 * $result will not be used; we'll register this callback multiple
			 * times and will want to gather overlapping results, so they'll
			 * be stored in $this->batch
			 */
			return array( array(), true );
		};
	}

	/**
	 * Collect referenced Title's from html content and add to LinkBatch
	 *
	 * @param string $content html to check for titles
	 */
	public function collectLinks( $content ) {
		if ( !$content ) {
			return;
		}
		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 * * mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		 */
		$dom = ParsoidUtils::createDOM( '<?xml encoding="utf-8" ?>' . $content );

		// find links in DOM
		$batch = $this->batch;
		$callback = function( DOMNode $linkNode, array $parsoid ) use( $batch ) {
			$title = Title::newFromText( $parsoid['sa']['href'] );
			if ( $title !== null ) {
				$batch->addObj( $title );
			}
		};
		self::forEachLink( $dom, $callback );
	}

	/**
	 * Parsoid ignores red links. With good reason: redlinks should only be
	 * applied when rendering the content, not when it's created.
	 *
	 * This method will parse a given content, fetch all of its links & let MW's
	 * Linker class build the link HTML (which will take redlinks into account.)
	 * It will then substitute original link HTML for the one Linker generated.
	 *
	 * @param string $content
	 * @return string
	 */
	public function apply( $content ) {
		if ( !$content ) {
			return '';
		}
		$section = new \ProfileSection( __METHOD__ );
		$this->resolveLinkStatus();

		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 * * mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		 */
		$dom = ParsoidUtils::createDOM( '<?xml encoding="utf-8"?>' . $content );
		$self = $this;
		self::forEachLink( $dom, function( DOMNode $linkNode, array $parsoid ) use ( $self, $dom ) {
			$title = $self->createRelativeTitle( $parsoid['sa']['href'] );
			// Don't process invalid links
			if ( $title === null ) {
				return;
			}

			// gather existing link attributes
			$attributes = array();
			foreach ( $linkNode->attributes as $attribute ) {
				$attributes[$attribute->name] = $attribute->value;
			}
			// let MW build link HTML based on Parsoid data
			$html = Linker::link( $title, Redlinker::getInnerHtml( $linkNode ), $attributes );
			// create new DOM from this MW-built link
			$replacementNode = ParsoidUtils::createDOM( '<?xml encoding="utf-8"?>' . $html )->getElementsByTagName( 'a' )->item( 0 );
			// import MW-built link node into content DOM
			$replacementNode = $dom->importNode( $replacementNode, true );
			// replace Parsoid link with MW-built link
			$linkNode->parentNode->replaceChild( $replacementNode, $linkNode );
		} );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( $body ) {
			$res = Redlinker::getInnerHtml( $body );
		} else {
			wfDebugLog( 'Flow', __METHOD__ . ' : Source content ' . md5( $content ) . ' resulted in no body' );
			$res = '';
		}
		return $res;
	}

	/**
	 * Execute pending batched title lookup
	 */
	public function resolveLinkStatus() {
		if ( !$this->identifiers ) {
			return;
		}
		foreach ( $this->identifiers as $identifier => $post ) {
			$post->getRecursiveResult( $identifier );
		}
		$this->identifiers = array();
		if ( !$this->batch->isEmpty() ) {
			$this->batch->execute();
			$this->batch->setArray( array() );
		}
	}

	/**
	 * Subpage links from parsoid don't contain any direct context, its applied via
	 * a <base href="..."> tag, so here we apply a similar rule resolving against
	 * $wgFlowParsoidTitle falling back to $wgTitle.
	 *
	 * @param string $text
	 * @return Title|null
	 */
	public function createRelativeTitle( $text ) {
		if ( $text && $text[0] === '/' ) {
			return Title::newFromText( $this->title->getDBkey() . $text, $this->title->getNamespace() );
		} else {
			return Title::newFromText( $text );
		}
	}

	/**
	 * Helper method executes a callback on every anchor that contains
	 * an ['sa']['href'] value in data-parsoid
	 *
	 * @param DOMDocument $dom
	 * @param Closure $callback Receives (DOMNode, array)
	 */
	static public function forEachLink( DOMDocument $dom, Closure $callback ) {
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//a[@rel="mw:WikiLink"][@data-parsoid]' );

		foreach ( $linkNodes as $linkNode ) {
			$parsoid = $linkNode->getAttribute( 'data-parsoid' );
			$parsoid = FormatJson::decode( $parsoid, true );
			if ( isset( $parsoid['sa']['href'] ) ) {
				$callback( $linkNode, $parsoid );
			}
		}
	}

	/**
	 * Helper method retrieves the html of the nodes children
	 *
	 * @param DOMNode $node
	 * @return string html of the nodes children
	 */
	static public function getInnerHtml( DOMNode $node = null ) {
		$html = array();
		if ( $node ) {
			$dom = $node->ownerDocument;
			foreach ( $node->childNodes as $child ) {
				$html[] = $dom->saveHTML( $child );
			}
		}
		return implode( '', $html );
	}
}

