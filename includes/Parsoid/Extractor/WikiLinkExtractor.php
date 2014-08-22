<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\Reference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;
use FormatJson;

class WikiLinkExtractor implements ExtractorInterface {
	public function getXPath() {
		return '//a[@rel="mw:WikiLink"][not(@typeof)]';
	}

	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		$parsoidData = FormatJson::decode( $element->getAttribute( 'data-parsoid' ), true );

		return $factory->createWikiReference( Reference::TYPE_LINK, $parsoidData['sa']['href'] );
	}
}
