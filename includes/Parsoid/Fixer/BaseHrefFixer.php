<?php

namespace Flow\Parsoid\Fixer;

use Flow\Parsoid\Fixer;
use MediaWiki\Title\Title;

/**
 * Parsoid markup expects a <base href> of //domain/wiki/ .
 * However, this would have to be added in the <head> and apply
 * to the whole page, which could affect other content.
 *
 * For now, we just apply this transformation to our own user
 * Parsoid content.  It does not need to be done for WikiLink, since
 * that is handled by WikiLinkFixer in another way.
 */
class BaseHrefFixer implements Fixer {
	/**
	 * @var string
	 */
	protected $baseHref;

	/**
	 * @param string $articlePath path setting for wiki
	 */
	public function __construct( $articlePath ) {
		$replacedArticlePath = str_replace( '$1', '', $articlePath );
		$this->baseHref = wfExpandUrl( $replacedArticlePath, PROTO_RELATIVE );
	}

	/**
	 * Returns XPath matching elements that need to be transformed
	 *
	 * @return string XPath of elements this acts on
	 */
	public function getXPath() {
		// WikiLinkFixer handles mw:WikiLink
		return '//a[@href and not(contains(concat(" ",normalize-space(@rel)," ")," mw:WikiLink "))]';
	}

	/**
	 * Prefixes the href with base href.
	 *
	 * @param \DOMNode $node Link
	 * @param Title $title
	 */
	public function apply( \DOMNode $node, Title $title ) {
		if ( !$node instanceof \DOMElement ) {
			return;
		}

		$href = $node->getAttribute( 'href' );
		if ( !str_starts_with( $href, './' ) ) {
			// If we need to handle more complex cases, we should resolve it
			// with a library like Net_URL2. This check will then be
			// unnecessary.
			return;
		}

		$href = $this->baseHref . $href;
		$node->setAttribute( 'href', $href );
	}
}
