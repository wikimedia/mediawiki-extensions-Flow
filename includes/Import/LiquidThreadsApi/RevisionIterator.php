<?php

namespace Flow\Import\LiquidThreadsApi;

use Flow\Import\IImportObject;
use Iterator;

/**
 * Iterates over the revisions of a foreign page to produce
 * revisions of a Flow object.
 */
class RevisionIterator implements Iterator {
	/** @var array */
	protected $pageData;

	protected int $pointer;

	/** @var IImportObject */
	protected $parent;

	/** @var callable */
	protected $factory;

	public function __construct( array $pageData, IImportObject $parent, callable $factory ) {
		$this->pageData = $pageData;
		$this->pointer = 0;
		$this->parent = $parent;
		$this->factory = $factory;
	}

	protected function getRevisionCount() {
		if ( isset( $this->pageData['revisions'] ) ) {
			return count( $this->pageData['revisions'] );
		} else {
			return 0;
		}
	}

	public function valid(): bool {
		return $this->pointer < $this->getRevisionCount();
	}

	public function next(): void {
		++$this->pointer;
	}

	public function key(): int {
		return $this->pointer;
	}

	public function rewind(): void {
		$this->pointer = 0;
	}

	public function current(): mixed {
		return ( $this->factory )( $this->pageData['revisions'][$this->pointer], $this->parent );
	}
}
