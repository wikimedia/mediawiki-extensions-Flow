<?php

namespace Flow;

use DOMXPath;
use Flow\Model\Reference;
use Flow\Model\URLReference;
use Flow\Model\UUID;
use Flow\Model\WikiReference;
use Flow\Model\Workflow;
use Title;
use MWException;

/**
 * Extracts references to templates, files and pages (in the form of links)
 * from Parsoid HTML.
 */
class ReferenceExtractor {
	protected $handlers;

	public function __construct( $handlers ) {
		$this->handlers = $handlers;
	}

	public function getReferences( Workflow $workflow, $objectType, UUID $objectId, $text ) {
		$referenceList = $this->extractReferences( $text );
		$references = array();

		foreach( $referenceList as $ref ) {
			if ( !$ref ) {
				continue;
			}
			$reference = $this->instantiateReference(
				$workflow,
				$objectType,
				$objectId,
				$ref['targetType'],
				$ref['refType'],
				$ref['target']
			);
			if ( $reference ) {
				$references[] = $reference;
			}
		}

		return $references;
	}

	public function extractReferences( $text ) {
		$dom = \Flow\Parsoid\Utils::createDOM( '<?xml encoding="utf-8" ?>' . $text );

		$output = array();

		$xpath = new DOMXPath( $dom );

		foreach( $this->handlers as $query => $handler ) {
			$elements = $xpath->query( $query );

			if ( ! $elements ) {
				throw new MWException( "No results for $query" );
			}

			foreach( $elements as $element ) {
				$output[] = $handler( $element );
			}
		}

		return $output;
	}

	public function instantiateReference( Workflow $workflow, $objectType, UUID $objectId, $targetType, $refType, $value ) {
		if ( $targetType === 'url' ) {
			return new URLReference(
				$workflow->getId(),
				$workflow->getArticleTitle(),
				$objectType,
				$objectId,
				$refType,
				$value
			);
		} elseif ( $targetType === 'wiki' ) {
			$title = Title::newFromText( $value );
			if ( $title === null ) {
				return null;
			}
			return new WikiReference(
				$workflow->getId(),
				$workflow->getArticleTitle(),
				$objectType,
				$objectId,
				$refType,
				$title
			);
		} else {
			throw new InvalidInputException( "Invalid type" );
		}
	}
}
