<?php

namespace Flow\Import\SourceStore;

use Flow\Import\IImportObject;
use Flow\Model\UUID;

interface SourceStoreInterface {
	/**
	 * Stores the association between an object and where it was imported from.
	 *
	 * @param UUID $objectId ID for the object that was imported.
	 * @param string $importSourceKey String returned from IImportObject::getObjectKey()
	 */
	function setAssociation( UUID $objectId, $importSourceKey );

	/**
	 * @param IImportObject $importObject
	 * @return UUID|bool UUID of the imported object if appropriate; otherwise, false.
	 */
	function getImportedId( IImportObject $importObject );

	/**
	 * Save any associations that have been added
	 * @throws Exception When save fails
	 */
	function save();

	/**
	 * Forget any recorded associations since last save
	 */
	function rollback();
}
