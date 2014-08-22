<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\WikiReference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;
use FormatJson;

class ImageExtractor implements ExtractorInterface {
	public function getXPath() {
		return '//*[starts-with(@typeof, "mw:Image")]';
	}

	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		$imgNode = $element->getElementsByTagName( 'img' )->item( 0 );
		$data = FormatJson::decode( $imgNode->getAttribute( 'data-parsoid' ), true );

		return $factory->createWikiReference( WikiReference::TYPE_FILE, $data['sa']['resource'] );
	}
}
