<?php

namespace Flow\Import;

use Flow\Model\UUID;

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
		$bytesWritten = file_put_contents( $this->filename, json_encode( $this->data ) );
		if ( $bytesWritten === false ) {
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

