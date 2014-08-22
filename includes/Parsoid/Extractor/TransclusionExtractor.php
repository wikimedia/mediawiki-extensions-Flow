<?php

namespace Flow\Parsoid\Extractor;

use DOMElement;
use Flow\Model\WikiReference;
use Flow\Parsoid\ExtractorInterface;
use Flow\Parsoid\ReferenceFactory;
use FormatJson;
use Title;

/**
 * Finds and creates References for transclusions in parsoid HTML
 */
class TransclusionExtractor implements ExtractorInterface {
	/**
	 * {@inheritDoc}
	 */
	public function getXPath() {
		return '//*[@typeof="mw:Transclusion"]';
	}

	/**
	 * {@inheritDoc}
	 */
	public function perform( ReferenceFactory $factory, DOMElement $element ) {
		$data = FormatJson::decode( $element->getAttribute( 'data-mw' ) );
		$templateTarget = Title::newFromText(
			$data->parts[0]->template->target->wt,
			NS_TEMPLATE
		);

		if ( !$templateTarget ) {
			return null;
		}

		return $factory->createWikiReference(
			WikiReference::TYPE_TEMPLATE,
			$templateTarget->getPrefixedText()
		);
	}
}
