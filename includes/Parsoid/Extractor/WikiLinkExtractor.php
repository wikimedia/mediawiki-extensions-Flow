<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\Reference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;
use FormatJson;

/**
 * Finds and creates References for internal wiki links in parsoid HTML
 */
class WikiLinkExtractor implements ExtractorInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getXPath() {
		return '//a[@rel="mw:WikiLink"][not(@typeof)]';
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		$href = $element->getAttribute( 'href' );
		if ( $href === '' ) {
			return null;
		}

		return $factory->createWikiReference(
			Reference::TYPE_LINK,
			urldecode( $href )
		);
	}
}
