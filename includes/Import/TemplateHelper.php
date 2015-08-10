<?php

namespace Flow\Import;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Flow\Parsoid\Utils;

class TemplateHelper {

	/**
	 * @param string $htmlContent
	 * @param string $templateName
	 * @return string
	 * @throws \Flow\Exception\WikitextException
	 */
	public static function removeFromHtml( $htmlContent, $templateName ) {
		$dom = Utils::createDOM( $htmlContent );
		$xpath = new DOMXPath( $dom );
		$templateNodes = $xpath->query( '//*[@typeof="mw:Transclusion"]' );

		foreach ( $templateNodes as $templateNode ) {
			/** @var DOMElement $templateNode */
			if ( $templateNode->hasAttribute( 'data-mw' ) ) {
				$name = self::getTemplateName( $templateNode->getAttribute( 'data-mw' ) );
				if ( $name === $templateName ) {
					$templateNode->parentNode->removeChild( $templateNode );
					if ( $templateNode->hasAttribute( 'about' ) ) {
						$about = $templateNode->getAttribute( 'about' );
						self::removeAboutNodes( $dom, $about );
					}
				}
			}
		}

		$body = $xpath->query( '/html/body' )->item(0);
		return $dom->saveHTML( $body );
	}

	/**
	 * @param string $dataMW
	 * @return string|null
	 */
	private static function getTemplateName( $dataMW ) {
		try {
			$mwAttr = json_decode( $dataMW );
			return $mwAttr->parts[0]->template->target->wt;
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * @param DOMDocument $dom
	 * @param string $about
	 */
	private static function removeAboutNodes( DOMDocument $dom, $about ) {
		$xpath = new DOMXPath( $dom );
		$aboutNodes = $xpath->query( '//*[@about="' . $about . '"]' );
		foreach ( $aboutNodes as $aboutNode ) {
			$aboutNode->parentNode->removeChild( $aboutNode );
		}
	}

}