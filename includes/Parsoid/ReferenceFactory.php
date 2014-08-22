<?php

namespace Flow\Parsoid;

use Flow\Model\URLReference;
use Flow\Model\UUID;
use Flow\Model\WikiReference;
use Flow\Model\Workflow;

class ReferenceFactory {
	/**
	 * @param Workflow $workflow
	 * @param string $objectType
	 * @param UUID $objectId
	 */
	public function __construct( Workflow $workflow, $objectType, UUID $objectId ) {
		$this->workflowId = $workflow->getId();
		$this->title = $workflow->getArticleTitle();
		$this->objectType = $objectType;
		$this->objectId = $objectId;
	}

	/**
	 * @param string $refType
	 * @param string $value
	 * @return URLReference
	 */
	public function createUrlReference( $refType, $value ) {
		return new URLReference(
			$this->workflowId,
			$$this->title,
			$this->objectType,
			$this->objectId,
			$refType,
			$value
		);
	}

	/**
	 * @param string $refType
	 * @param string $value
	 * @return WikiReference|null
	 */
	public function createWikiReference( $refType, $value ) {
		$title = Utils::createRelativeTitle( $value, $this->title );

		if ( $title === null ) {
			return null;
		}

		return new WikiReference(
			$this->workflowId,
			$this->title,
			$this->objectType,
			$this->objectId,
			$refType,
			$title
		);
	}
}
