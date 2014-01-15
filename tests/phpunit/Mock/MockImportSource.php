<?php

namespace Flow\Tests\Mock;

use ArrayIterator;
use Flow\Import\IImportSource;

class MockImportSource implements IImportSource {
	/** @var IImportTopic[] */
	protected $topics;

	/**
	 * @param IImportTopic[]
	 */
	public function __construct( array $topics = array() ) {
		$this->topics = $topics;
	}

	public function getTopics() {
		return new ArrayIterator( $this->topics );
	}
}
