<?php

namespace Flow\Import\SourceStore;

use Flow\Import\IImportObject;
use Flow\Model\UUID;

class Null implements SourceStoreInterface {
	public function setAssociation( UUID $objectId, $importSourceKey ) {
		return '';
	}

	public function getImportedId( IImportObject $importObject ) {
		return false;
	}

	public function save() {
	}

	public function rollback() {
	}
}
