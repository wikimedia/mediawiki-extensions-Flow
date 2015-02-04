<?php

namespace Flow\Parsoid\Fixer;

use DOMElement;
use DOMNode;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Parsoid\Fixer;
use Flow\Parsoid\Utils;
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

class BadImageRemover implements Fixer {
	/**
	 * @var callable
	 */
	protected $isFiltered;

	/**
	 * @param callable $isFiltered (string, Title) returning bool. First
	 *  argument is the image name to check. Second argument is the page on
	 *  which the image occurs. Returns true when the image should be filtered.
	 */
	public function __construct( $isFiltered = 'wfIsBadImage' ) {
		$this->isFiltered = $isFiltered;
	}

	/**
	 * @return string
	 */
	public function getXPath() {
		return '//span[@typeof="mw:Image"]//img[@resource]';
	}

	/**
	 * Receives an html string. It find all images and run them through
	 * wfIsBadImage() to determine if the image can be shown.
	 *
	 * @param DOMNode $node
	 * @param Title $title
	 * @throws FlowException
	 */
	public function apply( DOMNode $node, Title $title ) {
		if ( !$node instanceof DOMElement ) {
			return;
		}

		$resource = $node->getAttribute( 'resource' );
		if ( $resource === '' ) {
			return;
		}

		$image = Utils::createRelativeTitle( $resource, $title );
		if ( !$image ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Could not construct title for node: ' . $node->ownerDocument->saveXML( $node ) );
			return;
		}

		if ( !call_user_func( $this->isFiltered, $image->getDBkey(), $title ) ) {
			return;
		}

		// Move up the DOM and remove the typeof="mw:Image" node
		$nodeToRemove = $node->parentNode;
		while( $nodeToRemove instanceof DOMElement && $nodeToRemove->getAttribute( 'typeof' ) !== 'mw:Image' ) {
			$nodeToRemove = $nodeToRemove->parentNode;
		}
		if ( !$nodeToRemove ) {
			throw new FlowException( 'Did not find parent mw:Image to remove' );
		}
		$nodeToRemove->parentNode->removeChild( $nodeToRemove );
	}

	/**
	 * @param PostRevision $post
	 * @return bool
	 */
	public function isRecursive( PostRevision $post ) {
		return false;
	}

	/**
	 * Recursing doesn't make sense here, nothing to batch-load.
	 *
	 * @param DOMNode $node
	 */
	public function recursive( DOMNode $node ) {
		// nothing to do
	}

	/**
	 * Recursing doesn't make sense here, nothing to batch-load.
	 */
	public function resolve() {
		// nothing to do
	}
}
