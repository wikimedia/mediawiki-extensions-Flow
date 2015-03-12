<?php

namespace Flow\Parsoid\Fixer;

use Flow\Parsoid\Fixer;

/**
 * Parsoid markup expects a <base href> of //domain/wiki/ .
 * However, this would have to be added in the <head> and apply
 * to the whole page, which could affect other content.
 *
 * For now, we just apply this transformation to our own user
 * Parsoid content.  It does not need to be done for WikiLink, since
 * that is handled by Redlinker in another way.
 */
class BaseHrefFixer implements Fixer {
	/**
	 * Returns XPath matching elements that need to be transformed
	 */
	public function getXPath() {
		// Redlinker handles mw:WikiLink
		return '//a[@href and not(@rel="mw:WikiLink")]';
	}

	/**
	 * Prefixes the href with base href.
	 *
	 * @param DOMNode $node Link
	 * @param Title $title
	 */
	public function apply( \DOMNode $node, \Title $title ) {
		global $wgArticlePath;

		if ( !$node instanceof \DOMElement ) {
			return;
		}

		$href = $node->getAttribute( 'href' );
		if ( strpos( $href, './' ) !== 0 ) {
			// If we need to handle more complex cases, we should resolve it
			// with a library like Net_URL2. This check will then be
			// unnecessary.
			return;
		}

		$articlePath = str_replace( '$1', '', $wgArticlePath );
		$baseHref = wfExpandUrl( $articlePath, PROTO_RELATIVE );
		$href = $baseHref . $href;
		$node->setAttribute( 'href', $href );
	}
}