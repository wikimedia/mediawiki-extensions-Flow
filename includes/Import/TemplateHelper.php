<?php

namespace Flow\Import;

use DOMElement;
use DOMXPath;
use Flow\Parsoid\Utils;

class TemplateHelper {

	public static function removeFromHtml( $htmlContent, $templateName ) {
		$dom = Utils::createDOM( $htmlContent );
		$xpath = new DOMXPath( $dom );
		$templateNodes = $xpath->query( '//*[@typeof="mw:Transclusion"]' );

		foreach ( $templateNodes as $templateNode ) {
			/** @var DOMElement $templateNode */
			if ( $templateNode->hasAttribute( 'data-mw' ) ) {
				$mwAttr = json_decode( $templateNode->getAttribute( 'data-mw' ) );
				$name = $mwAttr->parts[0]->template->target->wt;

				if ( $name === $templateName ) {
					$templateNode->parentNode->removeChild( $templateNode );
				}

			}
		}

		$body = $xpath->query( '/html/body' )->item(0);
		return $dom->saveHTML( $body );
	}

}