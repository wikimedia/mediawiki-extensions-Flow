<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\UUID;
use Flow\DbFactory;
use BagOStuff;
use FormatJson;
use SplObjectStorage;
use Flow\Exception\DataModelException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\DataPersistenceException;
use Flow\Exception\NoIndexException;

/**
 * Writable indexes. Error handling is all wrong currently.
 */
class ObjectManager extends ObjectLocator {
	// @var SplObjectStorage $loaded If the object exists then an 'update' is issued, otherwise 'insert'
	protected $loaded;

	public function __construct( ObjectMapper $mapper, ObjectStorage $storage, array $indexes = array(), array $lifecycleHandlers = array() ) {
		parent::__construct( $mapper, $storage, $indexes, $lifecycleHandlers );

		// This needs to be SplObjectStorage rather than using spl_object_hash for keys
		// in a normal array because if the object gets GC'd spl_object_hash can reuse
		// the value.  Stuffing the object as well into SplObjectStorage prevents GC.
		$this->loaded = new SplObjectStorage;
	}

	public function put( $object ) {
		if ( isset( $this->loaded[$object] ) ) {
			$this->update( $object );
		} else {
			$this->insert( $object );
		}
	}

	/**
	 * merge an object loaded from outside the object manager for update.
	 * without merge it will be an insert.
	 */
	public function merge( $object ) {
		if ( !isset( $this->loaded[$object] ) ) {
			$this->loaded[$object] = $this->mapper->toStorageRow( $object );
		}
	}

	protected function insert( $object ) {
		$section = new \ProfileSection( __METHOD__ );
		$row = $this->mapper->toStorageRow( $object );
		$stored = $this->storage->insert( $row );
		if ( !$stored ) {
			throw new DataModelException( 'failed insert', 'process-data' );
		}
		// propogate auto-id's and such back into $object
		$this->mapper->fromStorageRow( $stored, $object );
		foreach ( $this->lifecycleHandlers as $handler ) {
			$handler->onAfterInsert( $object, $stored );
		}
		$this->loaded[$object] = $stored;
	}

	protected function update( $object ) {
		$section = new \ProfileSection( __METHOD__ );
		$old = $this->loaded[$object];
		$new = $this->mapper->toStorageRow( $object );
		if ( self::arrayEquals( $old, $new ) ) {
			return;
		}
		foreach ( $new as $k => $x ) {
			if ( $x !== null && !is_scalar( $x ) ) {
				throw new DataModelException( "Expected mapper to return all scalars, but '$k' is " . gettype( $x ), 'process-data' );
			}
		}
		$this->storage->update( $old, $new );
		foreach ( $this->lifecycleHandlers as $handler ) {
			$handler->onAfterUpdate( $object, $old, $new );
		}
		$this->loaded[$object] = $new;
	}

	public function remove( $object ) {
		$section = new \ProfileSection( __METHOD__ );
		$old = $this->loaded[$object];
		$this->storage->remove( $old );
		foreach ( $this->lifecycleHandlers as $handler ) {
			$handler->onAfterRemove( $object, $old );
		}
		unset( $this->loaded[$object] );
	}

	public function clear() {
		$this->loaded = new SplObjectStorage;
	}

	protected function load( $row ) {
		$object = parent::load( $row );
		$this->loaded[$object] = $row;
		return $object;
	}

	static public function arrayEquals( array $old, array $new ) {
		return array_diff_assoc( $old, $new ) === array()
			&& array_diff_assoc( $new, $old ) === array();
	}

	static public function makeArray( $input ) {
		if ( is_array( $input ) ) {
			return $input;
		} else {
			return array( $input );
		}
	}

	static public function calcUpdates( array $old, array $new ) {
		$updates = array();
		foreach ( array_keys( $new ) as $key ) {
			if ( !array_key_exists( $key, $old ) || $old[$key] !== $new[$key] ) {
				$updates[$key] = $new[$key];
			}
			unset( $old[$key] );
		}
		// These keys dont exist in $new
		foreach ( array_keys( $old ) as $key ) {
			$updates[$key] = null;
		}
		return $updates;
	}


	/**
	 * Separate a set of keys from an array. Returns null if not
	 * all keys are set.
	 *
	 * @param array $row
	 * @param array $keys
	 * @return array
	 */
	static public function splitFromRow( array $row, array $keys ) {
		$split = array();
		foreach ( $keys as $key ) {
			if ( !isset( $row[$key] ) ) {
				return null;
			}
			$split[$key] = $row[$key];
		}

		return $split;
	}

	/**
	 * @param object $object
	 * @param array $sortFields
	 * @return string
	 */
	public function serializeOffset( $object, array $sortFields ) {
		$offsetFields = array();
		$row = $this->mapper->toStorageRow( $object );
		foreach( $sortFields as $field ) {
			$value = $row[$field];

			if ( is_string( $value )
				&& strlen( $value ) === UUID::BIN_LEN
				&& substr( $field, -3 ) === '_id'
			) {
				$value = UUID::create( $value );
			}
			if ( $value instanceof UUID ) {
				$value = $value->getAlphadecimal();
			}
			$offsetFields[] = $value;
		}

		return implode( '|', $offsetFields );
	}

	public function multiPut( array $objects ) {
		throw new DataModelException( 'Not Implemented', 'process-data' );
	}

	public function multiDelete( array $objects ) {
		throw new DataModelException( 'Not Implemented', 'process-data' );
	}
}
