<?php

namespace Flow\Tests\Mock;

use Flow\Import\IObjectRevision;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;

class MockImportTopic extends MockImportPost implements IImportTopic {
	/**
	 * @var IImportSummary
	 */
	protected $summary;

	/**
	 * @param IImportSummary $summary
	 * @param IObjectRevision[] $revisions
	 * @param IImportPost[] $replies
	 */
	public function __construct( IImportSummary $summary = null, array $revisions, array $replies ) {
		parent::__construct( $revisions, $replies );
		$this->summary = $summary;
	}

	/**
	 * @inheritDoc
	 */
	public function getTopicSummary() {
		return $this->summary;
	}

	/**
	 * @inheritDoc
	 */
	public function getLogType() {
		"mock-flow-topic-import";
	}

	/**
	 * @inheritDoc
	 */
	public function getLogParameters() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getObjectKey() {
		return 'mock-topic:1';
	}
}
