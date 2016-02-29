<?php

namespace Flow\Import\SourceStore;

use Flow\Model\UUID;

interface SourceStoreInterface {
	/**
	 * Stores the association between an object and where it was imported from.
	 *
	 * @param UUID   $objectId		ID for the object that was imported.
	 * @param string $importSourceKey String returned from IImportObject::getObjectKey()
	 */
	function setAssociation( UUID $objectId, $importSourceKey );

	/**
	 * @param string $importSourceKey String returned from IImportObject::getObjectKey()
	 * @return UUID|boolean		   UUID of the imported object if appropriate; otherwise, false.
	 */
	function getImportedId( $importSourceKey );

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
