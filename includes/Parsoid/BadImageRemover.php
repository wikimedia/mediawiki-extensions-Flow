<?php

namespace Flow\Parsoid;

use Closure;
use DOMDocument;
use DOMNode;
use Flow\Model\PostRevision;
use Title;

/**
 * Parsoid ignores bad_image_list. With good reason: bad images should only be
 * removed when rendering the content, not when it's created. This
 * class updates HTML content from Parsoid by deleting inappropriate images, as
 * defined by wfIsBadImage().
 *
 * Usage:

 *	$badImageRemover = new BadImageRemover();
 *
 *	// Before outputting content
 *	$content = $badImageRemover->apply( $foo->getContent(), $title );
 */
class BadImageRemover implements ContentFixer {
	/**
	 * @var callable
	 */
	protected $isFiltered;

	/**
	 * @var callable $callback (string, Title) returning bool. First
	 *  argument is the image name to check. Second argument is the page on
	 *  which the image occurs. Returns true when the image should be filtered.
	 */
	public function __construct( $isFiltered ) {
		$this->isFiltered = $isFiltered;
	}

	/**
	 * Receives an html string. It find all images and run them through
	 * wfIsBadImage() to determine if the image can be shown.
	 *
	 * @param string $content
	 * @param Title $title
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
		 *
		 * The body tag is required otherwise <meta> tags at the top are
		 * magic'd into <head> rather than kept with the content.
		 */
		$dom = Utils::createDOM( '<?xml encoding="utf-8"?><body>' . $content . '</body>' );
		$isFiltered = $this->isFiltered;
		self::forEachImage( $dom, function( DOMNode $linkNode, $resource ) use ( $isFiltered, $dom, $title ) {
			$image = Utils::createRelativeTitle( $resource, $title );
			if ( !$image ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Could not construct title for node: ' . $dom->saveXML( $linkNode ) );
			} elseif ( call_user_func( $isFiltered, $image->getDBkey(), $title ) ) {

				// Move up the DOM and remove the typeof="mw:Image" node
				$nodeToRemove = $linkNode->parentNode;
				while( $nodeToRemove && $nodeToRemove->getAttribute( 'typeof' ) !== 'mw:Image' ) {
					$nodeToRemove = $nodeToRemove->parentNode;
				}
				if ( $nodeToRemove ) {
					$nodeToRemove->parentNode->removeChild( $nodeToRemove );
				}
			}
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
	 * Helper method executes a callback on every img with a resource
	 * attribute inside a span with the typeof="mw:Image" attribute.
	 *
	 * @param DOMDocument $dom
	 * @param Closure $callback Receives (DOMNode, string)
	 */
	static public function forEachImage( DOMDocument $dom, Closure $callback ) {
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//span[@typeof="mw:Image"]//img[@resource]' );

		/** @var DOMElement $linkNode */
		foreach ( $linkNodes as $linkNode ) {
			$resource = $linkNode->getAttribute( 'resource' );
			if ( $resource !== '' ) {
				$callback( $linkNode, $resource );
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

	/**
	 * Recursing doesn't make sense here, nothing to batch-load.
	 *
	 * @param PostRevision $post
	 * @param array $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function recursive( PostRevision $post, $result ) {
		return array( array(), false );
	}

	/**
	 * Recursing doesn't make sense here, nothing to batch-load.
	 *
	 * @param array $result
	 */
	public function resolve( $result ) {
		// nothing to do
	}
}
