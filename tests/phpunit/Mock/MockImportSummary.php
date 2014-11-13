<?php

namespace Flow\Tests\Mock;

use ArrayIterator;
use Flow\Import\IObjectRevision;
use Flow\Import\IImportSummary;
use User;

class MockImportSummary implements IImportSummary {
	/**
	 * @var IObjectRevision[]
	 */
	protected $revisions;

	/**
	 * @param IObjectRevision[] $revisions
	 */
	public function __construct( array $revisions = array() ) {
		$this->revisions = $revisions;
	}

	public function getRevisions() {
		return new ArrayIterator( $this->revisions );
	}

	public function getObjectKey() {
		return 'mock-summary:1';
	}
}
