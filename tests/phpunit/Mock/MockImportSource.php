<?php

namespace Flow\Tests\Mock;

use ArrayIterator;
use Flow\Import\IImportHeader;
use Flow\Import\IImportSource;
use Flow\Import\IImportTopic;

class MockImportSource implements IImportSource {
	/**
	 * @var IImportTopic[]
	 */
	private $topics;

	/**
	 * @var IImportHeader
	 */
	private $header;

	/**
	 * @param IImportHeader $header
	 * @param IImportTopic[] $topics
	 */
	public function __construct( IImportHeader $header, array $topics ) {
		$this->topics = $topics;
		$this->header = $header;
	}

	/**
	 * @inheritDoc
	 */
	public function getTopics() {
		return new ArrayIterator( $this->topics );
	}

	/**
	 * @inheritDoc
	 */
	public function getHeader() {
		return $this->header;
	}
}
