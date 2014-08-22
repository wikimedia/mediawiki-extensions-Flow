<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\WikiReference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;
use FormatJson;

/**
 * Finds and creates References for images in parsoid HTML
 */
class ImageExtractor implements ExtractorInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getXPath() {
		return '//*[starts-with(@typeof, "mw:Image")]';
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		$imgNode = $element->getElementsByTagName( 'img' )->item( 0 );
		$data = FormatJson::decode( $imgNode->getAttribute( 'data-parsoid' ), true );

		return $factory->createWikiReference( WikiReference::TYPE_FILE, $data['sa']['resource'] );
	}
}
