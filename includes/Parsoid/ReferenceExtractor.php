<?php

namespace Flow\Parsoid;

use DOMXPath;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use MWException;

/**
 * Extracts references to templates, files and pages (in the form of links)
 * from Parsoid HTML.
 */
class ReferenceExtractor {
	/**
	 * @var ExtractorInterface[]
	 */
	protected $extractors;

	/**
	 * @param ExtractorInterface[] $extractors
	 */
	public function __construct( $extractors ) {
		$this->extractors = $extractors;
	}

	/**
	 * @param Workflow $workflow
	 * @param string $objectType
	 * @param UUID $objectId
	 * @param string $text
	 * @return array
	 */
	public function getReferences( Workflow $workflow, $objectType, UUID $objectId, $text ) {
		return $this->extractReferences(
			new ReferenceFactory( $workflow, $objectType, $objectId ),
			$text
		);
	}

	protected function extractReferences( ReferenceFactory $factory, $text ) {
		$dom = Utils::createDOM( '<?xml encoding="utf-8" ?>' . $text );

		$output = array();

		$xpath = new DOMXPath( $dom );

		foreach( $this->extractors as $extractor ) {
			$elements = $xpath->query( $extractor->getXPath() );

			if ( ! $elements ) {
				$class = get_class( $extractor );
				throw new MWException( "Malformed xpath from $class: " . $extractor->getXPath() );
			}

			foreach( $elements as $element ) {
				$ref = $extractor->perform( $factory, $element );
				if ( $ref !== null ) {
					$output[] = $ref;
				}
			}
		}

		return $output;
	}
}
