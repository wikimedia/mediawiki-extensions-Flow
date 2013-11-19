<?php

namespace Flow;

abstract class ParsoidUtils {
	/**
	 * Convert form/to wikitext/html.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @return string
	 */
	public static function convert( $from, $to, $content ) {
		if ( $from === $to || $content === '' ) {
			return $content;
		}

		//throw new \MWException( "Attempt to round trip '$from' -> '$to'" );
		try {
			// use VE API (which connects to Parsoid) if available...
			return self::parsoid( $from, $to, $content );
		} catch ( NoParsoidException $e ) {
			// ... otherwise default to parser
			return self::parser( $from, $to, $content );
		}
	}

	/**
	 * Convert form/to wikitext/html using VisualEditor's API.
	 *
	 * This will assume Parsoid is installed, which is a dependency of VE.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @return string
	 */
	protected static function parsoid( $from, $to, $content ) {
		global $wgVisualEditorParsoidURL, $wgVisualEditorParsoidPrefix, $wgVisualEditorParsoidTimeout;

		if ( ! isset( $wgVisualEditorParsoidURL ) || ! $wgVisualEditorParsoidURL ) {
			throw new NoParsoidException( "VisualEditor parsoid configuration is unavailable" );
		}

		if ( $from == 'html' ) {
			$from = 'html';
		} elseif ( in_array( $from, array( 'wt', 'wikitext' ) ) ) {
			$from = 'wt';
		} else {
			throw new \MWException( 'Unknown source format: ' . $from );
		}

		$response = \Http::post(
			// @todo needs a big refactor to get a page title in here, fake Main_Page for now
			$wgVisualEditorParsoidURL . '/' . $wgVisualEditorParsoidPrefix . '/Main_Page',
			array(
				'postData' => array( $from => $content ),
				'timeout' => $wgVisualEditorParsoidTimeout
			)
		);

		if ( $response === false ) {
			throw new \MWException( 'Failed contacting parsoid' );
		}

		// Full HTML document is returned, we only want what's inside <body>
		if ( $to == 'html' ) {
			$dom = new \DOMDocument();
			$dom->loadHTML( $response );
			foreach ( $dom->getElementsByTagName( 'a' ) as $node ) {
				$attr = $node->attributes->getNamedItem( 'href' );
				if ( $attr !== null ) {
					self::fixAttrUrl( $attr );
				}
			}
			$body = $dom->getElementsByTagName( 'body' )->item( 0 );

			$response = '';
			foreach( $body->childNodes as $child ) {
				$response .= $child->ownerDocument->saveXML( $child );
			}
		} elseif ( !in_array( $to, array( 'wt', 'wikitext' ) ) ) {
			throw new \MWException( "Unknown format requested: " . $to );
		}

		return $response;
	}

	/**
	 * Convert form/to wikitext/html using Parser.
	 *
	 * This only supports wikitext to HTML.
	 *
	 * @param string $from Format of content to convert: wikitext
	 * @param string $to Format to convert to: html
	 * @param string $content
	 * @return string
	 */
	protected static function parser( $from, $to, $content ) {
		if ( $from !== 'wikitext' && $to !== 'html' ) {
			throw new \MWException( 'Parser only supports wikitext to HTML conversion' );
		}

		global $wgParser;

		// Bogus title used for parser
		$title = \Title::newMainPage();

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}

	/**
	 * Parsoid always prefixes links/images/etc with enough ../ to bring it
	 * back to /wiki/.  Here we strip the ../ and ./ and always give it /wiki/.
	 * This does ignore the existing <base href="...">, would be better to resolve
	 * it properly but not going to pull in a url library just to do it.
	 *
	 * @param DOMDocument $dom
	 * @param string $baseHref
	 */
	protected static function fixAttrUrl( \DOMAttr $attr ) {
		if ( $attr->value[0] !== '.' ) {
			// All relative links in parsoid start with '.'
			return;
		}
		// Strip leading ../../../../
		$partial = preg_replace( '|^(../)+|', '', $attr->value );
		// Strip leading ./
		if ( substr( $partial , 0, 2 ) === './' ) {
			$partial = substr( $partial, 2 );
		}
		// Update to always use /wiki/
		$attr->value = "/wiki/$partial";
	}
}

class NoParsoidExceptions extends \MWException {}

