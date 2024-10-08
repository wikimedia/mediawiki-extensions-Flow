<?php

namespace Flow\Parsoid\Fixer;

use Flow\Parsoid\Fixer;
use MediaWiki\Title\Title;
use MediaWiki\Utils\UrlUtils;

/**
 * Parsoid markup didn't always contain class="external" and rel="nofollow" where appropriate.
 * This is needed for correct styling and to ensure proper indexing,
 * so we add them here if they are missing.
 */
class ExtLinkFixer implements Fixer {

	private UrlUtils $urlUtils;

	public function __construct( UrlUtils $urlUtils ) {
		$this->urlUtils = $urlUtils;
	}

	/**
	 * Returns XPath matching elements that need to be transformed
	 *
	 * @return string XPath of elements this acts on
	 */
	public function getXPath() {
		return '//a[contains(concat(" ",normalize-space(@rel)," ")," mw:ExtLink ")]';
	}

	/**
	 * Adds class="external" & rel="nofollow" to external links.
	 *
	 * @param \DOMNode $node Link
	 * @param Title $title
	 */
	public function apply( \DOMNode $node, Title $title ) {
		if ( !$node instanceof \DOMElement ) {
			return;
		}
		$nodeClass = $node->getAttribute( 'class' );
		if ( strpos( ' ' . $nodeClass . ' ', ' external ' ) === false ) {
			$node->setAttribute( 'class', 'external' .
				( $nodeClass !== '' ? ' ' . $nodeClass : '' ) );
		}

		global $wgNoFollowLinks, $wgNoFollowDomainExceptions;
		if (
			$wgNoFollowLinks &&
			!$this->urlUtils->matchesDomainList( $node->getAttribute( 'href' ), $wgNoFollowDomainExceptions )
		) {
			$oldRel = $node->getAttribute( 'rel' );
			if ( strpos( ' ' . $oldRel . ' ', ' nofollow ' ) === false ) {
				$node->setAttribute( 'rel', 'nofollow' . ( $oldRel !== '' ? ' ' . $oldRel : '' ) );
			}
		}
	}
}
