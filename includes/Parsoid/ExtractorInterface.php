<?php

namespace Flow\Parsoid;

use DOMElement;

interface ExtractorInterface {
	/**
	 * @return string
	 */
	function getXPath();

	/**
	 * @param DOMElement $element
	 * @return array
	 */
	function perform( ReferenceFactory $factory, DOMElement $element );
}
