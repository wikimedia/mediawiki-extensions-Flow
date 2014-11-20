<?php

namespace Flow\Import\Basic;

use Flow\Import\IObjectRevision;

class ObjectRevision implements IObjectRevision {
	protected $text;
	protected $timestamp;
	protected $author;

	public function __construct( $text, $timestamp, $author ) {
		$this->text = $text;
		$this->timestamp = $timestamp;
		$this->author = $author;
	}

	public function getText() {
		return $this->text;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getAuthor() {
		return $this->author;
	}
}
