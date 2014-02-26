<?php

namespace Flow\Model;

use Flow\Model\AbstractRevision;
use MapCacheLRU;

/**
 * Cache any useful collection data
 */
class CollectionCache {

	/**
	 * Max to cache collection's last revision
	 */
	const LAST_REV_CACHE_MAX = 50;

	/**
	 * The last revision for a collection
	 *
	 * @var \MapCacheLRU
	 */
	protected $lastRevCache;

	/**
	 * Initialize any cache holder in here
	 */
	public function __construct() {
		$this->lastRevCache = new MapCacheLRU( self::LAST_REV_CACHE_MAX );
	}

	/**
	 * Get the last revision of a collection that the requested revision belongs to
	 * @param AbstractRevision $revision current revision
	 * @return AbstractRevision the last revision
	 */
	public function getLastRevisionFor( AbstractRevision $revision ) {
		$key = $revision->getCollectionId()->getBinary();
		$lastRevision = $this->lastRevCache->get( $key );
		if ( $lastRevision === null ) {
			$lastRevision = $revision->getCollection()->getLastRevision();
			$this->lastRevCache->set( $key, $lastRevision );
		}

		return $lastRevision;
	}

	public function onAfterLoad( $object, array $row ) {}

	public function onAfterInsert( $object, array $new ) {
		$this->lastRevCache->clear( $object->getCollectionId()->getBinary() );
	}
	public function onAfterUpdate( $object, array $old, array $new ) {
		$this->lastRevCache->clear( $object->getCollectionId()->getBinary() );
	}
	public function onAfterRemove( $object, array $old ) {
		$this->lastRevCache->clear( $object->getCollectionId()->getBinary() );
	}

}
