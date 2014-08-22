<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\Reference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;

class ExtLinkExtractor implements ExtractorInterface {
	public function getXPath() {
		return '//a[@rel="mw:ExtLink"]';
	}

	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		return $factory->createUrlReference(
			Reference::TYPE_LINK,
			urldecode( $element->getAttribute( 'href' ) )
		);
	}
}

