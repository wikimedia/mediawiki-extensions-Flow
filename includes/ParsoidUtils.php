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
	 * Convert from/to wikitext/html via Parsoid.
	 *
	 * This will assume Parsoid is installed.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @return string
	 */
	protected static function parsoid( $from, $to, $content ) {
		list( $parsoidURL, $parsoidPrefix, $parsoidTimeout ) = self::parsoidConfig();
		if ( !isset( $parsoidURL ) || !$parsoidURL ) {
			throw new NoParsoidException( 'Flow Parsoid configuration is unavailable' );
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
			$parsoidURL . '/' . $parsoidPrefix . '/Main_Page',
			array(
				'postData' => array( $from => $content ),
				'timeout' => $parsoidTimeout
			)
		);

		if ( $response === false ) {
			throw new \MWException( 'Failed contacting parsoid' );
		}

		// Full HTML document is returned, we only want what's inside <body>
		if ( $to == 'html' ) {
			/*
			 * Workaround because DOMDocument can't guess charset.
			 * Parsoid provides utf-8. Alternative "workarounds" would be to
			 * provide the charset in $response, as either:
			 * * <?xml encoding="utf-8" ?>
			 * * <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			 */
			$response = mb_convert_encoding( $response, 'HTML-ENTITIES', 'UTF-8' );

			$dom = self::createDOM( $response );
			$body = $dom->getElementsByTagName( 'body' )->item(0);

			$response = '';
			foreach( $body->childNodes as $child ) {
				$response .= $child->ownerDocument->saveHTML( $child );
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
	 * Returns Flow's Parsoid config. $wgFlowParsoid* variables can be used to
	 * specify a certain Parsoid installation. If none specified, we'll piggy-
	 * back on VisualEditor's Parsoid setup.
	 *
	 * @return array Parsoid config, in array(URL, prefix, timeout) format
	 */
	protected static function parsoidConfig() {
		global
			$wgFlowParsoidURL, $wgFlowParsoidPrefix, $wgFlowParsoidTimeout,
			$wgVisualEditorParsoidURL, $wgVisualEditorParsoidPrefix, $wgVisualEditorParsoidTimeout;

		return array(
			$wgFlowParsoidURL ? $wgFlowParsoidURL : $wgVisualEditorParsoidURL,
			$wgFlowParsoidPrefix ? $wgFlowParsoidPrefix : $wgVisualEditorParsoidPrefix,
			$wgFlowParsoidTimeout ? $wgFlowParsoidTimeout : $wgVisualEditorParsoidTimeout,
		);
	}

	/**
	 * Turns given $content string into a DOMDocument object.
	 *
	 * Some errors are forgivable (e.g. 801: there may be "invalid" HTML tags,
	 * like figcaption), so these can be ignored. Other errors will cause a
	 * warning.
	 *
	 * @param string $content
	 * @param array[optional] $ignoreErrorCodes
	 * @return \DOMDocument
	 * @throws \MWException
	 * @see http://www.xmlsoft.org/html/libxml-xmlerror.html
	 */
	public static function createDOM( $content, $ignoreErrorCodes = array( 801 ) ) {
		$dom = new \DOMDocument();

		// don't output warnings
		$useErrors = libxml_use_internal_errors( true );

		$dom->loadHTML( $content );

		// check error codes; if not in the supplied list of ignorable errors,
		// throw an exception
		$errors = libxml_get_errors();
		foreach ( $errors as $error ) {
			if ( !in_array( $error->code, $ignoreErrorCodes ) ) {
				throw new \MWException( $error->message );
			}
		}

		// restore libxml error reporting
		libxml_clear_errors();
		libxml_use_internal_errors( $useErrors );

		return $dom;
	}
}

class NoParsoidException extends \MWException {}
