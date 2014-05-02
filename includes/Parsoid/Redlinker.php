<?php

namespace Flow\Parsoid;

use Flow\Model\PostRevision;
use ArrayObject;
use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;
use LinkBatch;
use Linker;
use Title;
use FormatJson;

/**
 * Parsoid ignores red links. With good reason: redlinks should only be
 * applied when rendering the content, not when it's created. This
 * class updates HTML content from Parsoid with anchors generated by
 * Linker::link.
 *
 * An optional first stage, collectLinks, allows batching together the
 * db lookup for red vs blue status.  It receives an html string and
 * finds all anchor's which contain Parsoid Title strings and loads
 * those Title's into a LinkBatch. Additionally the register method
 * applies collectLinks to a post and its children recursively.
 *
 * The second stage, apply, receives an html string. It find all
 * anchor's which contain Parsoid Title strings and replaces them with
 * Linker::link generated anchors.  Current attributes and anchor
 * content are passed into Linker::link.
 *
 * Usage:
 *
 *	$redlinker = new Redlinker( new LinkBatch );
 *
 *	// Collect links to batch from html content directly
 *	foreach ( $foos as $foo ) {
 *	    $redlinker->collectLinks( $foo->getContent() );
 *	}
 *
 *	// Alternatively, the register method will read the content of a
 *	// post and all its children recursively.
 *	$redlinker->registerPost( $topicPost ); @todo fix this
 *
 *	// Before outputting content
 *	$content = $redlinker->apply( $foo->getContent(), Title::newMainPage() );
 */
class Redlinker implements ContentFixer {
	/**
	 * @var LinkBatch
	 */
	protected $batch;

	/**
	 * @param LinkBatch $batch
	 */
	public function __construct( LinkBatch $batch ) {
		$this->batch = $batch;
		$this->processed = new ArrayObject;
	}

	/**
	 * @param PostRevision $post
	 * @param array $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function recursive( PostRevision $post, $result ) {
		// topic titles don't contain html
		if ( $post->isFormatted() ) {
			$this->collectLinks( $post->getContent( 'html' ) );
		}

		/*
		 * $result will not be used; we'll register this callback multiple
		 * times and will want to gather overlapping results, so they'll
		 * be stored in $this->batch
		 */
		return array( array(), true );
	}

	/**
	 * Execute pending batched title lookup.
	 *
	 * @param array $result
	 */
	public function resolve( $result ) {
		// $result is not used here. The recursive function has saved the "real"
		// data in $this->batch.

		if ( !$this->batch->isEmpty() ) {
			$this->batch->execute();
			$this->batch->setArray( array() );
		}
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
		$dom = Utils::createDOM( '<?xml encoding="utf-8" ?>' . $content );

		// find links in DOM
		$batch = $this->batch;
		$callback = function( DOMNode $linkNode, array $parsoid ) use( $batch ) {
			$title = Utils::getLinkTarget( $parsoid );
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
	 * @param Title $title Title to resolve relative links against
	 * @return string
	 */
	public function apply( $content, Title $title ) {
		if ( !$content ) {
			return '';
		}
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );

		/*
		 * Workaround because DOMDocument can't guess charset.
		 * Content should be utf-8. Alternative "workarounds" would be to
		 * provide the charset in $response, as either:
		 * * <?xml encoding="utf-8" ?>
		 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		 * * mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		 */
		$dom = Utils::createDOM( '<?xml encoding="utf-8"?>' . $content );
		$self = $this;
		self::forEachLink( $dom, function( DOMNode $linkNode, array $parsoid ) use ( $self, $dom, $title ) {
			$title = Utils::createRelativeTitle( $parsoid['sa']['href'], $title );
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
			$replacementNode = Utils::createDOM( '<?xml encoding="utf-8"?>' . $html )->getElementsByTagName( 'a' )->item( 0 );
			// import MW-built link node into content DOM
			$replacementNode = $dom->importNode( $replacementNode, true );
			// replace Parsoid link with MW-built link
			$linkNode->parentNode->replaceChild( $replacementNode, $linkNode );
		} );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( $body ) {
			$res = self::getInnerHtml( $body );
		} else {
			wfDebugLog( 'Flow', __METHOD__ . ' : Source content ' . md5( $content ) . ' resulted in no body' );
			$res = '';
		}
		return $res;
	}

	/**
	 * Helper method executes a callback on every anchor that contains
	 * an ['sa']['href'] value in data-parsoid
	 *
	 * @param DOMDocument $dom
	 * @param Closure $callback Receives (DOMElement, array)
	 */
	static public function forEachLink( DOMDocument $dom, Closure $callback ) {
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//a[@rel="mw:WikiLink"][@data-parsoid]' );

		foreach ( $linkNodes as $linkNode ) {
			// $linkNodes can contain any DOMNode, not just DOMElement's
			if ( $linkNode instanceof DOMElement ) {
				$parsoid = $linkNode->getAttribute( 'data-parsoid' );
				$parsoid = FormatJson::decode( $parsoid, true );
				if ( isset( $parsoid['sa']['href'] ) ) {
					$callback( $linkNode, $parsoid );
				}
			} else {
				wfDebugLog( 'Flow', __METHOD__ . ': Expected DOMElement but received: ' . get_class( $linkNode ) );
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

