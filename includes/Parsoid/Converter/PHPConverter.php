<?php

namespace Flow\Parsoid\Converter;

use Flow\Exception\WikitextException;
use Flow\Parsoid\ContentConverter;
use Title;

class PHPConverter implements ContentConverter {

	/**
	 * {@inheritDoc}
	 */
	public function getRequiredModules() {
		return array();
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
	 * @throws WikitextException When the conversion is unsupported
	 */
	public function convert( $from, $to, $content, Title $title ) {
		if ( $from === $to || $content === '' ) {
			return $content;
		}
		if ( $from !== 'wikitext' && $to !== 'html' ) {
			throw new WikitextException( 'Parser only supports wikitext to HTML conversion', 'process-wikitext' );
		}

		global $wgParser;

		$options = new \ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );

		$output = $wgParser->parse( $content, $title, $options );
		return $output->getText();
	}
}
