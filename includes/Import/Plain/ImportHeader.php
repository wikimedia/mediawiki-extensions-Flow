<?php

namespace Flow\Import\Plain;

use ArrayIterator;
use Flow\Import\IImportHeader;

class ImportHeader implements IImportHeader {
	public function __construct( array $revisions ) {
		$this->revisions = $revisions;
	}

	public function getRevisions() {
		return new ArrayIterator( $this->revisions );
	}
}
