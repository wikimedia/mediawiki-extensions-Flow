<?php

namespace Flow\Tests\Mock;

use ArrayIterator;
use Flow\Import\IImportPost;
use User;

class MockImportPost implements IImportPost {
	/** @var array */
	protected $attribs;

	public function __construct( array $attribs = array() ) {
		$this->attribs = $attribs + array(
			'author' => User::newFromName( '127.0.0.1', false ),
			'createdTimestamp' => time(),
			'text' => 'something something',
			'replies' => array(),
		);
		if ( !isset( $this->attribs['modifiedTimestamp'] ) ) {
			$this->attribs['modifiedTimestamp'] = $this->attribs['createdTimestamp'];
		}
	}

	public function getAuthor() {
		return $this->attribs['author'];
	}

	public function getCreatedTimestamp() {
		return $this->attribs['createdTimestamp'];
	}

	public function getModifiedTimestamp() {
		return $this->attribs['modifiedTimestamp'];
	}

	public function getText() {
		return $this->attribs['text'];
	}

	public function getReplies() {
		return new ArrayIterator( $this->attribs['replies'] );
	}
}
