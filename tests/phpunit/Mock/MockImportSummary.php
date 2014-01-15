<?php

namespace Flow\Tests\Mock;

use Flow\Import\IImportSummary;
use User;

class MockImportSummary implements IImportSummary {
	/** @var array */
	protected $attribs;

	public function __construct( array $attribs = array() ) {
		$this->attribs = $attribs + array(
			'author' => User::newFromName( '127.0.0.1', false ),
			'text' => 'summarized!',
			'created' => time(),
		);
	}

	public function getAuthor() {
		return $this->attribs['author'];
	}

	public function getText() {
		return $this->attribs['text'];
	}

	public function getCreatedTimestamp() {
		return $this->attribs['created'];
	}

	public function getObjectKey() {
		return 'mock-summary:1';
	}
}
