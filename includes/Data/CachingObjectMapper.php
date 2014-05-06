<?php

namespace Flow\Data;

use Flow\Model\UUID;

class CachingObjectMapper implements ObjectMapper {
	protected $toStorageRow;

	protected $fromStorageRow;

	protected $loaded;

	public function __construct( $toStorageRow, $fromStorageRow, array $primaryKey ) {
		$this->toStorageRow = $toStorageRow;
		$this->fromStorageRow = $fromStorageRow;
		ksort( $primaryKey );
		$this->primaryKey = $primaryKey;
		$this->clear();
	}

	static public function model( $className, array $primaryKey ) {
		return new self(
			array( $className, 'toStorageRow' ),
			array( $className, 'fromStorageRow' ),
			$primaryKey
		);
	}

	public function toStorageRow( $object ) {
		$row = call_user_func( $this->toStorageRow, $object );
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			// new object may not have pk yet, calling code
			// should call self::fromStorageRow with $object to load
			// db assigned pk and store obj in $this->loaded
		} elseif ( !isset( $this->loaded[$pk] ) ) {
			// first time this id has been seen
			$this->loaded[$pk] = $object;
		} elseif ( $this->loaded[$pk] !== $object ) {
			// loaded object of this id is not same object
			$class = get_class( $object );
			$id = json_encode( $pk );
			throw new \InvalidArgumentException( "Duplicate '$class' objects for id $id" );
		}
		return $row;
	}

	public function fromStorageRow( array $row, $object = null ) {
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			throw new \InvalidArgumentException( 'Storage row has no pk' );
		} elseif ( !isset( $this->loaded[$pk] ) ) {
			// unserialize the object
			return $this->loaded[$pk] = call_user_func( $this->fromStorageRow, $row, $object );
		} elseif ( $object === null ) {
			// provide previously loaded object
			return $this->loaded[$pk];
		} elseif ( $object !== $this->loaded[$pk] ) {
			// loaded object of this id is not same object
			$class = get_class( $object );
			$id = json_encode( $pk );
			throw new \InvalidArgumentException( "Duplicate '$class' objects for id $id" );
		} else {
			// object was provided, load $row into $object
			// we already know $this->loaded[$pk] === $object
			return call_user_func( $this->fromStorageRow, $row, $object );
		}
	}

	/**
	 * @param array $primaryKey
	 * @return object|null
	 * @throws \InvalidArgumentException
	 */
	public function get( array $primaryKey ) {
		$primaryKey = UUID::convertUUIDs( $primaryKey, 'alphadecimal' );
		ksort( $primaryKey );
		if ( array_keys( $primaryKey ) !== $this->primaryKey ) {
			throw new \InvalidArgumentException;
		}
		try {
			return $this->loaded[$primaryKey];
		} catch ( \OutOfBoundsException $e ) {
			return null;
		}
	}

	public function clear() {
		$this->loaded = new MultiDimArray;
	}
}
