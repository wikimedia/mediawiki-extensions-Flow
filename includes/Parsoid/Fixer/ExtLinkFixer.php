<?php

namespace Flow\Parsoid\Fixer;

use Flow\Parsoid\Fixer;

/**
 * Parsoid markup doesn't contain class="external" for external links. This is needed for
 * correct styling, so we add it here.
 */
class ExtLinkFixer implements Fixer {
	/**
	 * Returns XPath matching elements that need to be transformed
	 *
	 * @return string XPath of elements this acts on
	 */
	public function getXPath() {
		return '//a[@rel="mw:ExtLink"]';
	}

	/**
	 * Prefixes the href with base href.
	 *
	 * @param DOMNode $node Link
	 * @param Title $title
	 */
	public function apply( \DOMNode $node, \Title $title ) {
		if ( !$node instanceof \DOMElement ) {
			return;
		}

		$node->setAttribute( 'class', 'external' );

		global $wgNoFollowLinks, $wgNoFollowDomainExceptions;
		if ( $wgNoFollowLinks && !wfMatchesDomainList( $node->getAttribute( 'href' ), $wgNoFollowDomainExceptions ) ) {
			$node->setAttribute( 'rel', 'nofollow' );
		}
	}
}
