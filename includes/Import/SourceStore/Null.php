<?php

namespace Flow\Import\SourceStore;

use Flow\Model\UUID;

class Null implements SourceStoreInterface {
	public function setAssociation( UUID $objectId, $importSourceKey ) {
	}

	public function getImportedId( $importSourceKey ) {
		return false;
	}

	public function save() {
	}

	public function rollback() {
	}
}

