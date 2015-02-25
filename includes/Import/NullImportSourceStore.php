<?php

namespace Flow\Import;

use Flow\Model\UUID;

class NullImportSourceStore implements ImportSourceStore {
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
