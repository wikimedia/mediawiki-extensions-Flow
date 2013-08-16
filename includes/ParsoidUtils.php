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
		if ( $from === $to ) {
			return $content;
		}

		try {
			// use VE API (which connects to Parsoid) if available...
			return self::parsoid( $from, $to, $content );
		} catch ( \Exception $e ) {
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
		if ( !class_exists( 'ApiVisualEditor' ) ) {
			throw new \MWException( 'VisualEditor is unavailable' );
		}

		if ( $to === 'html' ) {
			$action = 'parsefragment';
		} elseif ( $to === 'wikitext' ) {
			$action = 'serialize';
		} else {
			throw new \MWException( 'Unknown format: '. $to );
		}

		global $wgRequest;
		$params = new \DerivativeRequest(
			$wgRequest,
			array(
				'action' => 'visualeditor',
				'page' => \SpecialPage::getTitleFor( 'Flow' )->getPrefixedDBkey(),
				// 'basetimestamp' => ?,
				// 'starttimestamp' => ?,
				'paction' => $action,
				$from => $content,
			),
			true // POST
		);

		$api = new \ApiMain( $params, true );
		$api->execute();
		$result = $api->getResultData();

		if ( !isset( $result['visualeditor']['content'] ) ) {
			throw new \MWException( 'Unable to parse content' );
		}

		return $result['visualeditor']['content'];
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

		$title = \Title::newFromText( 'Flow', NS_SPECIAL );

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}
}
