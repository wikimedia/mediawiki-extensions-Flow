<?php

namespace Flow\Parsoid\Fixer;

use DOMElement;
use DOMNode;
use Flow\Conversion\Utils;
use Flow\Parsoid\Fixer;
use HtmlArmor;
use MediaWiki\Cache\LinkBatch;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Parsoid ignores red links. With good reason: redlinks should only be
 * applied when rendering the content, not when it's created.
 *
 * This class updates HTML content from Parsoid with anchors generated by
 * LinkRenderer.  In addition to handling red links, this normalizes
 * relative paths to start with a /, so the HTML renders correctly
 * on any page.
 */
class WikiLinkFixer implements Fixer {
	/**
	 * @var LinkBatch
	 */
	protected $batch;

	/**
	 * @param LinkBatch $batch
	 */
	public function __construct( LinkBatch $batch ) {
		$this->batch = $batch;
	}

	/**
	 * @return string
	 */
	public function getXPath() {
		return '//a[contains(concat(" ",normalize-space(@rel)," ")," mw:WikiLink ")]';
	}

	/**
	 * Parsoid ignores red links. With good reason: redlinks should only be
	 * applied when rendering the content, not when it's created.
	 *
	 * This method will parse a given content, fetch all of its links & let MW's
	 * LinkRenderer build the link HTML (which will take redlinks into account.)
	 * It will then substitute original link HTML for the one LinkRenderer generated.
	 *
	 * This replaces both existing and non-existent anchors because the relative links
	 * output by parsoid are not usable when output within a subpage.
	 *
	 * @param DOMNode $node
	 * @param Title $title Title to resolve relative links against
	 * @throws \Flow\Exception\WikitextException
	 */
	public function apply( DOMNode $node, Title $title ) {
		if ( !$node instanceof DOMElement ) {
			return;
		}

		$href = $node->getAttribute( 'href' );
		if ( $href === '' ) {
			return;
		}

		$title = Utils::createRelativeTitle( rawurldecode( $href ), $title );
		if ( $title === null ) {
			return;
		}

		// gather existing link attributes
		$attributes = [];
		foreach ( $node->attributes as $attribute ) {
			$attributes[$attribute->name] = $attribute->value;
		}
		// let MW build link HTML based on Parsoid data
		$html = MediaWikiServices::getInstance()->getLinkRenderer()->makeLink(
			$title,
			new HtmlArmor( Utils::getInnerHtml( $node ) ),
			$attributes
		);
		// create new DOM from this MW-built link
		$replacementNode = Utils::createDOM( $html )
			->getElementsByTagName( 'a' )
			->item( 0 );
		// import MW-built link node into content DOM
		// @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
		$replacementNode = $node->ownerDocument->importNode( $replacementNode, true );
		// replace Parsoid link with MW-built link
		$node->parentNode->replaceChild( $replacementNode, $node );
	}
}
