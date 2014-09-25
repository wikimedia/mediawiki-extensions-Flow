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
	public function __construct( $isFiltered = 'wfIsBadImage' ) {
		$this->isFiltered = $isFiltered;
	}

	/**
	 * Receives an html string. It find all images and run them through
	 * wfIsBadImage() to determine if the image can be shown.
	 *
	 * @param DOMDocument $dom
	 * @param Title $title
	 */
	public function apply( DOMDocument $dom, Title $title ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );

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
