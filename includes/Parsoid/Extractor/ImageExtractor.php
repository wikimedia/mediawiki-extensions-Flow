<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\WikiReference;
use Flow\Parsoid\Extractor;
use Flow\Parsoid\ReferenceFactory;

/**
 * Finds and creates References for images in parsoid HTML
 */
class ImageExtractor implements Extractor {
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
		foreach ( $element->getElementsByTagName( 'img' ) as $item ) {
			if ( !$item instanceof DOMElement ) {
				continue;
			}
			$resource = $item->getAttribute( 'resource' );
			if ( $resource !== '' ) {
				return $factory->createWikiReference(
					WikiReference::TYPE_FILE,
					$resource
				);
			}
		}

		return null;
	}
}
