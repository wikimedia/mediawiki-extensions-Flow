<?php

namespace Flow\Model;

use Flow\Model\AbstractCollection;
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
	 * Get the last revision for a collection
	 * @param AbstractCollection
	 * @return AbstractRevision
	 */
	public function getLastRevisionFor( AbstractCollection $collection ) {
		$key = $collection->getId()->getBinary();
		if ( !$this->lastRevCache->has( $key ) ) {
			$this->lastRevCache->set( $key, $collection->getLastRevision() );
		}
		return $this->lastRevCache->get( $key );
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
