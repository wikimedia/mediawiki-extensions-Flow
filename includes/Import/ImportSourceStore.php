<?php

namespace Flow\Import;

use Flow\Exception\FlowException;
use Flow\Model\UUID;

interface ImportSourceStore {
	/**
	 * Stores the association between an object and where it was imported from.
	 *
	 * @param UUID   $objectId        ID for the object that was imported.
	 * @param string $importSourceKey String returned from IImportObject::getObjectKey()
	 */
	function setAssociation( UUID $objectId, $importSourceKey );

	/**
	 * @param string $importSourceKey String returned from IImportObject::getObjectKey()
	 * @return UUID|boolean           UUID of the imported object if appropriate; otherwise, false.
	 */
	function getImportedId( $importSourceKey );

	/**
	 * Save any associations that have been added
	 * @throws ImportSourceStoreException When save fails
	 */
	function save();

	/**
	 * Forged any recorded associations since last save
	 */
	function rollback();
}

class FileImportSourceStore implements ImportSourceStore {
	/** @var string **/
	protected $filename;
	/** @var array */
	protected $data;

	public function __construct( $filename ) {
		$this->filename = $filename;
		$this->load();
	}

	protected function load() {
		if ( file_exists( $this->filename ) ) {
			$this->data = json_decode( file_get_contents( $this->filename ), true );
		} else {
			$this->data = array();
		}
	}

	public function save() {
		if ( !file_put_contents( $this->filename, json_encode( $this->data ) ) ) {
			throw new ImportSourceStoreException( 'Could not write out source store to ' . $this->filename );
		}
	}

	public function rollback() {
		$this->load();
	}

	public function setAssociation( UUID $objectId, $importSourceKey ) {
		$this->data[$importSourceKey] = $objectId->getAlphadecimal();
	}

	public function getImportedId( $importSourceKey ) {
		return isset( $this->data[$importSourceKey] )
			? UUID::create( $this->data[$importSourceKey] )
			: false;
	}
}

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

class ImportSourceStoreException extends ImportException {
}
