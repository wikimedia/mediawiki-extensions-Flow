<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportObject;
use Iterator;

class RevisionIterator implements Iterator {
	/** @var array **/
	protected $pageData;

	/** @var int **/
	protected $pointer;

	/** @var IImportObject **/
	protected $parent;

	public function __construct( array $pageData, IImportObject $parent, $factory = null ) {
		$this->pageData = $pageData;
		$this->pointer = 0;
		$this->parent = $parent;
		$this->factory = $factory ?: function( $data, $parent ) {
			return new ImportRevision( $data, $parent );
		};
	}

	protected function getRevisionCount() {
		if ( isset( $this->pageData['revisions'] ) ) {
			return count( $this->pageData['revisions'] );
		} else {
			return 0;
		}
	}

	public function valid() {
		return $this->pointer < $this->getRevisionCount();
	}

	public function next() {
		++$this->pointer;
	}

	public function key() {
		return $this->pointer;
	}

	public function rewind() {
		$this->pointer = 0;
	}

	public function current() {
		return call_user_func(
			$this->factory,
			$this->pageData['revisions'][$this->pointer],
			$this->parent
		);
	}
}

