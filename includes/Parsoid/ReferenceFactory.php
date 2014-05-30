<?php

namespace Flow\Parsoid;

use Flow\Model\URLReference;
use Flow\Model\UUID;
use Flow\Model\WikiReference;
use Flow\Model\Workflow;
use Title;

class ReferenceFactory {
	/**
	 * @var string
	 */
	protected $wikiId;

	/**
	 * @var UUID
	 */
	protected $workflowId;

	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $objectType;

	/**
	 * @var UUID
	 */
	protected $objectId;

	/**
	 * @param String $wikiId Wiki identifier
	 * @param Workflow $workflow
	 * @param string $objectType
	 * @param UUID $objectId
	 */
	public function __construct( $wikiId, Workflow $workflow, $objectType, UUID $objectId ) {
		$this->wikiId = $wikiId;
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
			$this->wikiId,
			$this->workflowId,
			$this->title,
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
			$this->wikiId,
			$this->workflowId,
			$this->title,
			$this->objectType,
			$this->objectId,
			$refType,
			$title
		);
	}
}
