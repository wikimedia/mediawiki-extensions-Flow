<?php

namespace Flow\Parsoid;

use Flow\Exception\NoParsoidException;
use Flow\Exception\WikitextException;
use Title;

interface ContentConverter {
	/**
     * Convert from/to wikitext/html
     *
     * @param string $from Format of content to convert: html|wikitext
     * @param string $to Format to convert to: html|wikitext
     * @param string $content
     * @param Title $title
     * @return string
     * @throws NoParsoidException When there are errors contacting parsoid
     * @throws WikitextException When conversion is unsupported by the converter
     */
	function convert( $from, $to, $content, Title $title );

	/**
	 * @return array A list of resource loader modules that need to be included
	 *  with content converter by this converter.
	 */
	function getRequiredModules();
}
