<?php

namespace Flow\Tests\Mock;

use Flow\Import\IImportTopic;

class MockImportTopic extends MockImportPost implements IImportTopic {
	public function __construct( array $attribs = array() ) {
		parent::__construct( $attribs + array(
			'summary' => null,
		) );
	}

	public function getTopicSummary() {
		return $this->attribs['summary'];
	}

	public function getObjectKey() {
		return 'mock-topic:1';
	}
}
