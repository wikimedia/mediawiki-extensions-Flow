<?php

namespace Flow\Parsoid;

use Closure;
use DOMDocument;
use DOMNode;
use Title;
use FormatJson;

/**
 * Parsoid ignores bad_image_list. With good reason: redlinks should only be
 * applied when rendering the content, not when it's created. This
 * class updates HTML content from Parsoid by deleting inappropriate images, as
 * defined by wfIsBadImage().
 *
 * Usage:

 *	$badImageRemover = new BadImageRemover();
 *
 *	// Before outputting content
 *	$content = $badImageRemover->apply( $foo->getContent(), $title );
 */
class BadImageRemover {
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
		self::forEachImage( $dom, function( DOMNode $linkNode, array $parsoid ) use ( $self, $dom, $title ) {
			$image = Title::newFromDBkey( $parsoid['sa']['resource'], NS_FILE );
			if ( wfIsBadImage( $image->getDBkey(), $title ) ) {
				$linkNode->parentNode->removeChild( $linkNode );
			}
		} );

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( $body ) {
			$res = self::getInnerHtml( $body );
		} else {
			wfDebugLog( __CLASS__, __FUNCTION__ . ' : Source content ' . md5( $content ) . ' resulted in no body' );
			$res = '';
		}
		return $res;
	}

	/**
	 * Helper method executes a callback on every img that contains
	 * an ['sa']['resource'] value in data-parsoid
	 *
	 * @param DOMDocument $dom
	 * @param Closure $callback Receives (DOMNode, array)
	 */
	static public function forEachImage( DOMDocument $dom, Closure $callback ) {
		$xpath = new \DOMXPath( $dom );
		$linkNodes = $xpath->query( '//img[@data-parsoid]' );

		foreach ( $linkNodes as $linkNode ) {
			$parsoid = $linkNode->getAttribute( 'data-parsoid' );
			$parsoid = FormatJson::decode( $parsoid, true );
			if ( isset( $parsoid['sa']['resource'] ) ) {
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
