<?php

namespace Flow;

use Title;

abstract class ParsoidUtils {
	/**
	 * Convert from/to wikitext/html.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @param Title[optional] $title
	 * @return string
	 */
	public static function convert( $from, $to, $content, Title $title = null ) {
		if ( $from === $to || $content === '' ) {
			return $content;
		}

		if ( !$title instanceof Title ) {
			$title = Title::newMainPage();
		} elseif ( !$title->exists() ) {
			// Parsoid will fail if title does not exist
			throw new \MWException( 'Title "' . $title->getPrefixedDBkey() . '" does not exist.' );
		}

		try {
			// use VE API (which connects to Parsoid) if available...
			return self::parsoid( $from, $to, $content, $title );
		} catch ( NoParsoidException $e ) {
			// ... otherwise default to parser
			return self::parser( $from, $to, $content, $title );
		}
	}

	/**
	 * Convert from/to wikitext/html via Parsoid, piggy-backing on
	 * VisualEditor's globals.
	 *
	 * This will assume Parsoid is installed, which is a dependency of VE.
	 *
	 * @param string $from Format of content to convert: html|wikitext
	 * @param string $to Format to convert to: html|wikitext
	 * @param string $content
	 * @param Title $title
	 * @return string
	 */
	protected static function parsoid( $from, $to, $content, Title $title ) {
		global $wgVisualEditorParsoidURL, $wgVisualEditorParsoidPrefix, $wgVisualEditorParsoidTimeout;

		if ( !isset( $wgVisualEditorParsoidURL ) || ! $wgVisualEditorParsoidURL ) {
			throw new NoParsoidException( 'VisualEditor Parsoid configuration is unavailable' );
		}

		if ( $from == 'html' ) {
			$from = 'html';
		} elseif ( in_array( $from, array( 'wt', 'wikitext' ) ) ) {
			$from = 'wt';
		} else {
			throw new \MWException( 'Unknown source format: ' . $from );
		}

		$response = \Http::post(
			$wgVisualEditorParsoidURL . '/' . $wgVisualEditorParsoidPrefix . '/' . $title->getPrefixedDBkey(),
			array(
				'postData' => array( $from => $content ),
				'timeout' => $wgVisualEditorParsoidTimeout
			)
		);

		if ( $response === false ) {
			throw new \MWException( 'Failed contacting Parsoid' );
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

			$dom = new \DOMDocument();
			$dom->loadHTML( $response );
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
	 * Convert from/to wikitext/html using Parser.
	 *
	 * This only supports wikitext to HTML.
	 *
	 * @param string $from Format of content to convert: wikitext
	 * @param string $to Format to convert to: html
	 * @param string $content
	 * @param Title $title
	 * @return string
	 */
	protected static function parser( $from, $to, $content, Title $title ) {
		if ( $from !== 'wikitext' && $to !== 'html' ) {
			throw new \MWException( 'Parser only supports wikitext to HTML conversion' );
		}

		global $wgParser;

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}
}

class NoParsoidExceptions extends \MWException {}
