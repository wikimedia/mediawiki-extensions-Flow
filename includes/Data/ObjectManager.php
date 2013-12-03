<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\UUID;
use Flow\DbFactory;
use BagOStuff;
use RuntimeException;
use SplObjectStorage;

// Perhaps rethink lifecycle interface.  Simpler.
// Indexes need access to the cache and the backend storage.
// It seems likely different Indexes could use different caches (redis/memcache)
// - But we want to be able to replace that cache with a buffered cache that flushes
//   on db commit.
// - Perhaps one buffered cache could wrap both redis and memcache? seems odd though

interface LifecycleHandler {
	function onAfterLoad( $object, array $old );
	function onAfterInsert( $object, array $new );
	function onAfterUpdate( $object, array $old, array $new );
	function onAfterRemove( $object, array $old );
}

// Some denormalized data doesnt accept writes, it merely triggers cache updates
// when something else does the write. Indexes are the primary use case.
// IteratorAggregate rather than traversable to simplify nested implementations
interface ObjectStorage extends \IteratorAggregate {
	function find( array $attributes, array $options = array() );
	/**
	 * The BagOStuff interface returns with keys matching the key, unfortunately
	 * we deal with composite keys which makes that awkward. Instead all findMulti
	 * implementations must return their result as if it was array_map( array( $obj, 'find' ), $queries ).
	 * This is necessary so result sets stay ordered
	 *
	 *
	 * @param array $queries list of queries to perform
	 * @param array $options Options to use for all queries
	 * @return array
	 */
	function findMulti( array $queries, array $options = array() );
	function getPrimaryKeyColumns();
	// Clear any information stored about loaded objects
	// This interface is used by the frontend (ObjectLocator) and the backend (BasicDbStorage, etc)
	//function clear();
}

// Backing stores, typically in SQL
// Note that while ObjectLocator implements the above ObjectStorage interface, ObjectManger
// cant use this interface because backing stores deal in rows, and OM deals in objects.
interface WritableObjectStorage extends ObjectStorage {
	/**
	 * @return array The resulting $row including any auto-assigned ids or false on failure
	 */
	function insert( array $row );
	function update( array $old, array $new );
	function remove( array $row );
}

interface ObjectMapper {
	/**
	 * Convert $object from the domain model to its db row
	 */
	function toStorageRow( $object );

	/**
	 * Convert a db row to its domain model. Object passing is intended for
	 * updating the object to match a changed storage representation.
	 *
	 * @param array $row assoc array representing the domain model
	 * @param object|null $object The domain model to populate, creates when null
	 * @return object The domain model populated with $row
	 * @throws Exception When object is the wrong class for the mapper
	 */
	function fromStorageRow( array $row, $object = null );
}

/**
 * Indexes store one or more values bucketed by exact key/value combinations.
 */
interface Index extends LifecycleHandler {
	/**
	 * Find data models matching the provided equality condition.
	 *
	 * @param array $keys A map of k,v pairs to find via equality condition
	 * @return array|false Cached subset of data model rows matching the
	 *     equality conditions provided in $keys.
	 */
	function find( array $keys );

	/**
	 * Batch together multiple calls to self::find with minimal network round trips.
	 *
	 * @param array $queries An array of arrays in the form of $keys parameter of self::find
	 * @return array|false Array of arrays in same order as $queries representing batched result set.
	 */
	function findMulti( array $queries );

	/**
	 * @return integer Maximum number of items in a single index value
	 */
	function getLimit();

	/**
	 * Query options are not supported at the query level, the index always
	 * returns the same value for the same key/value combination.  Depending on what
	 * the query stores it may contain the answers to various options, which will require
	 * post-processing by the caller.
	 *
	 * @return boolean Can the index locate a result for this keys and options pair
	 */
	function canAnswer( array $keys, array $options );
}

/**
 * Compact rows before writing to memcache, expand when receiving back
 * Still returns arrays, just removes unneccessary values
 */
interface Compactor {
	/**
	 * @param array $row A data model row to strip unnecessary data from
	 * @return array Only the values in $row that will be written to the cache
	 */
	public function compactRow( array $row );

	/**
	 * @param array $rows Multiple data model rows to strip unnecesssary data from
	 * @return array The provided rows now containing only the values the will be written to cache
	 */
	public function compactRows( array $rows );

	/**
	 * Repopulate BagOStuff::multiGet results with any values removed in self::compactRow
	 *
	 * @param array $cached The multi-dimensional array results of BagOStuff::multiGet
	 * @param array $keyToQuery An array mapping memcache-key to the values used to generate that cache key
	 * @return array The cached content from memcache along with any data stripped in self::compactRow
	 */
	public function expandCacheResult( array $cached, array $keyToQuery );
}


/**
 * A little glue code to allow passing around and manipulating multiple
 * ObjectManagers more convenient.
 */
class ManagerGroup {
	public function __construct( Container $container, array $classMap ) {
		$this->container = $container;
		$this->classMap = $classMap;
	}

	public function getStorage( $className ) {
		if ( !isset( $this->classMap[$className] ) ) {
			throw new \MWException( "Request for '$className' is not in classmap: " . implode( ', ', array_keys( $this->classMap ) ) );
		}

		return $this->container[$this->classMap[$className]];
	}

	public function put( $object ) {
		return $this->getStorage( get_class( $object ) )->put( $object );
	}

	protected function call( $method, $args ) {
		$className = array_shift( $args );

		return call_user_func_array(
			array( $this->getStorage( $className ), $method ),
			$args
		);
	}

	public function get( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function getMulti( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function find( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function findMulti( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}
}

/**
 * Denormalized indexes that are query-only.  The indexes used here must
 * be provided to some ObjectManager as a lifecycleHandler to receive
 * update events.
 * Error handling is all wrong, but simplifies prototyping.
 */
class ObjectLocator implements ObjectStorage {
	protected $mapper;
	protected $storage;
	protected $indexes;
	protected $lifecycleHandlers;

	public function __construct( ObjectMapper $mapper, ObjectStorage $storage, array $indexes = array(), array $lifecycleHandlers = array() ) {
		$this->mapper = $mapper;
		$this->storage = $storage;
		$this->indexes = $indexes;
		$this->lifecycleHandlers = array_merge( $indexes, $lifecycleHandlers );
	}

	public function find( array $attributes, array $options = array() ) {
		$result = $this->findMulti( array( $attributes ), $options );
		return $result ? reset( $result ) : null;
	}

	public function getIterator() {
		return $this->storage->getIterator();
	}

	/**
	 * All queries must be against the same index. Results are equivilent to
	 * array_map, maintaining order and key relationship between input $queries
	 * and $result.
	 *
	 * @return array|null  null is query failure.  empty array is no result.  array is success
	 */
	public function findMulti( array $queries, array $options = array() ) {
		if ( !$queries ) {
			return array();
		}
		$keys = array_keys( reset( $queries ) );
		if ( isset( $options['sort'] ) && !is_array( $options['sort'] ) ) {
			$options['sort'] = ObjectManager::makeArray( $options['sort'] );
		}

		$index = $this->getIndexFor( $keys, $options );
		$res = $index->findMulti( $queries );

		if ( $res === null ) {
			return null;
		}

		$retval = array();
		foreach ( $res as $i => $rows ) {
			list( $startPos, $limit ) = $this->getOffsetLimit( $rows, $index, $options );
			$keys = array_keys( $rows );
			for(
				$k = $startPos;
				$k < $startPos + $limit && $k < count( $keys );
				++$k
			) {
				$j = $keys[$k];
				$retval[$i][$j] = $this->load( $rows[$j] );
			}
		}
		return $retval;
	}

	public function getPrimaryKeyColumns() {
		return $this->storage->getPrimaryKeyColumns();
	}

	public function get( $id ) {
		$result = $this->getMulti( array( $id ) );
		return $result ? reset( $result ) : null;
	}

	// Just a helper to find by primary key
	//
	// Be careful with regards to order on composite primary keys,
	// must be in same order as provided to the storage implementation.
	public function getMulti( array $objectIds ) {
		if ( !$objectIds ) {
			return array();
		}
		$pk = $this->storage->getPrimaryKeyColumns();
		$queries = array();
		foreach ( $objectIds as $id ) {
			$queries[] = array_combine( $pk, ObjectManager::makeArray( $id ) );
		}
		// primary key is unique, but indexes still return their results as array
		// to be consistent. undo that for a flat result array
		$res = $this->findMulti( $queries );
		if ( !$res ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $row ) {
			$retval[] = reset( $row );
		}
		return $retval;
	}

	protected function getOffsetLimit( $rows, $index, $options ) {
		$limit = isset( $options['limit'] ) ? $options['limit'] : $index->getLimit();

		if ( ! isset( $options['offset-key'] ) ) {
			$offset = isset( $options['offset'] ) ? $options['offset'] : 0;
			return array( $offset, $limit );
		}

		$offsetKey = $options['offset-key'];
		if ( $offsetKey instanceof UUID ) {
			$offsetKey = $offsetKey->getBinary();
		}

		$dir = 'fwd';
		if (
			isset( $options['offset-dir'] ) &&
			$options['offset-dir'] === 'rev'
		) {
			$dir = 'rev';
		}

		$offset = $this->getOffsetFromKey( $rows, $offsetKey, $index );

		if ( $dir === 'fwd' ) {
			$startPos = $offset + 1;
		} elseif ( $dir === 'rev' ) {
			$startPos = $offset - $limit;

			if ( $startPos < 0 ) {
				if (
					isset( $options['offset-elastic'] ) &&
					$options['offset-elastic'] === false
				) {
					// If non-elastic, then reduce the number of items shown commeasurately
					$limit += $startPos;
				}
				$startPos = 0;
			}
		}

		return array( $startPos, $limit );
	}

	protected function getOffsetFromKey( $rows, $offsetKey, $index ) {
		$offset = false;
		for( $rowIndex = 0; $rowIndex < count( $rows ); ++$rowIndex ) {
			$row = $rows[$rowIndex];
			$comparisonValue = $index->compareRowToOffset( $row, $offsetKey );
			if ( $comparisonValue <= 0 ) {
				$offset = $rowIndex;
				break;
			}
		}

		if ( $offset === false ) {
			throw new \MWException( "Unable to find specified offset in query results" );
		}

		return $offset;
	}

	public function clear() {
		// nop, we dont store anything
	}

	/**
	 * Only use from maintenance.  Updates not implemented(yet), needs
	 * handling for transactions.
	 */
	public function visitAll( $callback ) {
		// storage is commonly IteratorAggregate returning EchoBatchRowIterator of
		// 500 rows at a time by default.
		foreach ( $this->storage as $rows ) {
			foreach ( $rows as $row ) {
				call_user_func( $callback, $this->load( $row ) );
			}
			$this->clear();
		}
	}

	public function getIndexFor( array $keys, array $options = array() ) {
		sort( $keys );
		$current = null;
		foreach ( $this->indexes as $index ) {
			if ( !$index->canAnswer( $keys, $options ) ) {
				continue;
			}
			if ( !isset( $options['limit'] ) ) {
				return $index;
			}
			// Find the smallest matching index
			if ( $current === null || $index->getLimit() < $current->getLimit() ) {
				$current = $index;
			}
		}
		if ( $current === null ) {
			$count = count( $this->indexes );
			throw new \MWException(
				"No index (out of $count) available to answer query for " . implode( ", ", $keys ) .
				' with options ' . json_encode( $options )
			);
		}
		return $current;
	}

	protected function load( $row ) {
		$object = $this->mapper->fromStorageRow( $row );
		foreach ( $this->lifecycleHandlers as $handler ) {
			$handler->onAfterLoad( $object, $row );
		}
		return $object;
	}
}

/**
 * Writable indexes. Error handling is all wrong currently.
 */
class ObjectManager extends ObjectLocator {
	// @var SplObjectStorage $loaded If the object exists then an 'update' is issued, otherwise 'insert'
	protected $loaded;

	public function __construct( ObjectMapper $mapper, WritableObjectStorage $storage, array $indexes = array(), array $lifecycleHandlers = array() ) {
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
		try {
			$row = $this->mapper->toStorageRow( $object );
			$stored = $this->storage->insert( $row );
			if ( !$stored ) {
				throw new \MWException( 'failed insert' );
			}
			// propogate auto-id's and such back into $object
			$this->mapper->fromStorageRow( $stored, $object );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterInsert( $object, $stored );
			}
			$this->loaded[$object] = $stored;
		} catch ( \MWException $e ) {
			throw new PersistenceException( 'failed insert', null, $e );
		}
	}

	protected function update( $object ) {
		try {
			$old = $this->loaded[$object];
			$new = $this->mapper->toStorageRow( $object );
			if ( self::arrayEquals( $old, $new ) ) {
				return;
			}
			foreach ( $new as $k => $x ) {
				if ( $x !== null && !is_scalar( $x ) ) {
					throw new \RuntimeException( "Expected mapper to return all scalars, but '$k' is " . gettype( $x ) );
				}
			}
			$this->storage->update( $old, $new );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterUpdate( $object, $old, $new );
			}
			$this->loaded[$object] = $new;
		} catch ( \MWException $e ) {
			throw new PersistenceException( 'failed update', null, $e );
		}
	}

	public function remove( $object ) {
		try {
			$old = $this->loaded[$object];
			$this->storage->remove( $old );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterRemove( $object, $old );
			}
			unset( $this->loaded[$object] );
		} catch ( \MWException $e ) {
			throw new PersistenceException( 'failed remove', null, $e );
		}
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

	public function serializeOffset( $object, $sortFields ) {
		$offsetFields = array();
		$row = $this->mapper->toStorageRow( $object );
		foreach( $sortFields as $field ) {
			$value = $row[$field];

			if ( strlen( $value ) == 16 && preg_match( '/_id$/', $field ) ) {
				$value = UUID::create( $value )->getHex();
			}
			$offsetFields[] = $value;
		}

		return implode( '|', $offsetFields );
	}

	public function multiPut( array $objects ) {
		throw new \MWException( 'Not Implemented' );
	}

	public function multiDelete( array $objects ) {
		throw new \MWException( 'Not Implemented' );
	}
}
class PersistenceException extends \MWException {
}

/**
 * $userMapper = new BasicObjectMapper(
 *     array( 'User', 'toStorageRow' ),
 *     array( 'User', 'fromStorageRow' ),
 * );
 */
class BasicObjectMapper implements ObjectMapper {
	public function __construct( $toStorageRow, $fromStorageRow ) {
		$this->toStorageRow = $toStorageRow;
		$this->fromStorageRow = $fromStorageRow;
	}

	static public function model( $className ) {
		return new self( array( $className, 'toStorageRow' ), array( $className, 'fromStorageRow' ) );
	}

	public function toStorageRow( $object ) {
		return call_user_func( $this->toStorageRow, $object );
	}
	public function fromStorageRow( array $row, $object = null ) {
		return call_user_func( $this->fromStorageRow, $row, $object );
	}
}

/**
 * Standard backing store for data model with no special cases which is stored
 * in a single table in mysql.
 *
 * Doesn't support updating primary key value yet
 * Doesn't support auto-increment pk yet
 */
class BasicDbStorage implements WritableObjectStorage {
	public function __construct( DbFactory $dbFactory, $table, array $primaryKey ) {
		if ( !$primaryKey ) {
			throw new \MWException( 'PK required' );
		}
		$this->dbFactory = $dbFactory;
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}

	// Does not support auto-increment id yet
	public function insert( array $row ) {
		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( $this->hasRawSQL( $row ) ) {
			throw new \MWException( "Raw SQL found in row" );
		}

		// insert returns boolean true/false
		$res = $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->table,
			$row,
			__METHOD__ . " ({$this->table})"
		);
		if ( $res ) {
			return $row;
		} else {
			return false;
		}
	}

	public function update( array $old, array $new ) {
		$pk = ObjectManager::splitFromRow( $old, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $old ) );
			throw new PersistenceException( 'Row has null primary key: ' . implode( $missing ) );
		}
		$updates = ObjectManager::calcUpdates( $old, $new );
		if ( !$updates ) {
			return true; // nothing to change, success
		}

		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( $this->hasRawSQL( $updates ) || $this->hasRawSQL( $pk ) ) {
			throw new \MWException( "Raw SQL found in input" );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		// update returns boolean true/false as $res
		$res = $dbw->update( $this->table, $updates, UUID::convertUUIDs( $pk ), __METHOD__ . " ({$this->table})" );
		// $dbw->update returns boolean true/false as $res
		// we also want to check that $pk actually selected a row to update
		return $res && $dbw->affectedRows();
	}

	/**
	 * @return boolean success
	 */
	public function remove( array $row ) {
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $row ) );
			throw new PersistenceException( 'Row has null primary key: ' . implode( $missing ) );
		}

		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( $this->hasRawSQL( $pk ) ) {
			throw new \MWException( "Raw SQL found in PK" );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->delete( $this->table, UUID::convertUUIDs( $pk ), __METHOD__ . " ({$this->table})" );
		return $res && $dbw->affectedRows();
	}

	/*
	 * @return array|null  Empty array means no result,  null means query failure.  Array with results is
	 *                     success.
	 */
	public function find( array $attributes, array $options = array() ) {
		wfDebug( "Running search on table {$this->table}\n" );

		foreach( $attributes as $key => $value ) {
			if ( $value instanceof \Flow\Model\UUID ) {
				$value = $value->getHex();
			}
			wfDebug( " -- $key = $value\n" );
		}

		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( $this->hasRawSQL( $attributes ) ) {
			throw new \MWException( "Raw SQL found in select condition" );
		}

		$res = $this->dbFactory->getDB( DB_MASTER )->select(
			$this->table,
			'*',
			UUID::convertUUIDs( $attributes ),
			__METHOD__ . " ({$this->table})",
			$options
		);
		if ( ! $res ) {
			return null;
		}
		$result = array();
		foreach ( $res as $row ) {
			$result[] = (array) $row;
		}
		// wfDebugLog( __CLASS__, __METHOD__ . ': ' . print_r( $result, true ) );
		return $result;
	}

	protected function fallbackFindMulti( array $queries, array $options ) {
		$result = array();
		foreach ( $queries as $key => $query ) {
			$result[$key] = $this->find( $query, $options );
		}
		return $result;
	}

	public function findMulti( array $queries, array $options = array() ) {
		// TODO
		return $this->fallbackFindMulti( $queries, $options );
	}

	/**
	 * Only use from maintenance and debugging
	 */
	public function getIterator( $batchSize = 500 ) {
		return new \EchoBatchRowIterator(
			$this->dbFactory->getDB( DB_MASTER ),
			$this->table,
			$this->primaryKey,
			$batchSize
		);
	}

	public function getPrimaryKeyColumns() {
		return $this->primaryKey;
	}

	/**
	 * Internal security function which checks a row object
	 * (for inclusion as a condition or a row for insert/update)
	 * for any numeric keys (= raw SQL), or field names with
	 * potentially unsafe characters.
	 * @param  array   $row The row to check.
	 * @return boolean      True if raw SQL is found
	 */
	protected function hasRawSQL( array $row ) {
		foreach( $row as $key => $value ) {
			if ( is_numeric( $key ) ) {
				return true;
			}

			if ( ! preg_match( '/^[A-Za-z0-9\._]+$/', $key ) ) {
				return true;
			}
		}

		return false;
	}
}

/**
 * Index objects with equal features($indexedColumns) into the same buckets.
 */
abstract class FeatureIndex implements Index {

	protected $cache;
	protected $storage;
	protected $prefix;
	protected $rowCompactor;
	protected $indexed;
	protected $indexedOrdered;

	// This exists in the Index interface and as such cant be abstract
	// untill php 5.3.9, but some of our test machines are on 5.3.3
	//abstract public function getLimit();
	abstract public function queryOptions();
	abstract public function limitIndexSize( array $values );
	abstract protected function addToIndex( array $indexed, array $row );
	abstract protected function removeFromIndex( array $indexed, array $row );

	/**
	 * @param BufferedCache $cache
	 * @param ObjectStorage $storage
	 * @param string        $prefix
	 * @param array         $indexedColumns List of columns to index,
	 */
	public function __construct( BufferedCache $cache, ObjectStorage $storage, $prefix, array $indexedColumns ) {
		$this->cache = $cache;
		$this->storage = $storage;
		$this->prefix = $prefix;
		$this->rowCompactor = new FeatureCompactor( $indexedColumns );
		$this->indexed = $indexedColumns;
		// sort this and ksort in self::cacheKey to always have cache key
		// fields in same order
		sort( $indexedColumns );
		$this->indexedOrdered = $indexedColumns;
	}

	/**
	 * This must be in the provided order so portions of the application can
	 * array_combine( $index->getPrimaryKeyColumns(), $primaryKeyValues )
	 */
	public function getPrimaryKeyColumns() {
		return $this->indexed;
	}

	public function canAnswer( array $featureColumns, array $options ) {
		sort( $featureColumns );
		if ( $featureColumns !== $this->indexedOrdered ) {
			return false;
		}
		if ( isset( $options['limit'] ) ) {
			$max = $options['limit'];
			if ( isset( $options['offset'] ) ) {
				$max += $options['offset'];
			}
			if ( $max > $this->getLimit() ) {
				return false;
			}
		}
		return true;
	}

	public function getSort() {
		return isset( $this->options['sort'] ) ? $this->options['sort'] : false;
	}

	public function compareRowToOffset( $row, $offset ) {
		$sortFields = $this->getSort();
		$splitOffset = explode( '|', $offset );
		$fieldIndex = 0;

		if ( $sortFields === false ) {
			throw new MWException( "This Index implementation does not support key offsets" );
		}

		foreach( $sortFields as $field ) {
			$valueInRow = $row[$field];
			$valueInOffset = $splitOffset[$fieldIndex];

			if ( $valueInRow > $valueInOffset ) {
				return 1;
			} elseif ( $valueInRow < $valueInOffset ) {
				return -1;
			}
			++$fieldIndex;
		}

		return 0;
	}

	public function onAfterInsert( $object, array $new ) {
		$indexed = ObjectManager::splitFromRow( $new , $this->indexed );
		// is un-indexable a bail-worthy occasion? Probably not but makes debugging easier
		if ( !$indexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $new ) );
		}
		$compacted = $this->rowCompactor->compactRow( $new );
		// give implementing index option to create rather than append
		if ( !$this->maybeCreateIndex( $indexed, $new, $compacted ) ) {
			// fallback to append
			$this->addToIndex( $indexed, $compacted );
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$oldIndexed = ObjectManager::splitFromRow( $old, $this->indexed );
		$newIndexed = ObjectManager::splitFromRow( $new, $this->indexed );
		if ( !$oldIndexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $oldIndexed ) );
		}
		if ( !$newIndexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $newIndexed ) );
		}
		$oldCompacted = $this->rowCompactor->compactRow( $old );
		$newCompacted = $this->rowCompactor->compactRow( $new );
		if ( ObjectManager::arrayEquals( $oldIndexed, $newIndexed ) ) {
			if ( ObjectManager::arrayEquals( $oldCompacted, $newCompacted ) ) {
				// Nothing changed in the index
				return;
			}
			// object representation in feature bucket has changed
			$this->replaceInIndex( $oldIndexed, $oldCompacted, $newCompacted );
		} else {
			// object has moved from one feature bucket to another
			$this->removeFromIndex( $oldIndexed, $oldCompacted );
			$this->addToIndex( $newIndexed, $newCompacted );
		}
	}

	public function onAfterRemove( $object, array $old ) {
		$indexed = ObjectManager::splitFromRow( $old, $this->indexed );
		if ( !$indexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $old ) );
		}
		$this->removeFromIndex( $indexed, $old );
	}

	public function onAfterLoad( $object, array $old ) {
		// nothing to do
	}

	public function find( array $attributes ) {
		$results = $this->findMulti( array( $attributes ) );
		return reset( $results );
	}

	public function findMulti( array $queries ) {
		if ( !$queries ) {
			return array();
		}

		// Build a map from cache key to its index in $queries
		$results = $keyToIdx = $keyToQuery = array();
		foreach ( $queries as $idx => $query ) {
			ksort( $query );
			if ( array_keys( $query ) !== $this->indexedOrdered ) {
				throw new \MWException(
					'Cannot answer query for columns: ' . implode( ', ', array_keys( $queries[$idx] ) )
				);
			}
			$key = $this->cacheKey( $query );
			// allow for duplicate queries
			$keyToIdx[$key][] = $idx;
			$idxToKey[$idx] = $key;
			if ( !isset( $keyToQuery[$key] ) ) {
				// These results will be merged into the query results, and as such need binary
				// uuid's as would be received from storage
				$keyToQuery[$key] = UUID::convertUUIDs( $query );
			}
		}

		// Retreive from cache
		$cached = $this->cache->getMulti( array_keys( $keyToIdx ) );
		// expand partial results and merge into result set
		foreach( $this->rowCompactor->expandCacheResult( $cached, $keyToQuery ) as $key => $rows ) {
			foreach ( $keyToIdx[$key] as $idx ) {
				$results[$idx] = $rows;
				unset( $queries[$idx] );
			}
		}
		// dont need to query backing store
		if ( count( $queries ) === 0 ) {
			return $results;
		}

		return $this->backingStoreFindMulti( $queries, $idxToKey, $results );
	}

	protected function backingStoreFindMulti( array $queries, array $idxToKey, array $results = array() ) {
		// query backing store
		$stored = $this->storage->findMulti( $queries, $this->queryOptions() );
		// map store results to cache key
		foreach ( $stored as $idx => $rows ) {
			if ( !$rows ) {
				// Nothing found,  should we cache failures as well as success?
				continue;
			}
			foreach( $rows as $row ) {
				foreach ( $row as $k => $foo ) {
					if ( $foo !== null && !is_scalar( $foo ) ) {
						throw new \MWException( "Received non-scalar row value for '$k' from: " . get_class( $this->storage ) );
					}
				}
			}
			$this->cache->add( $idxToKey[$idx], $this->rowCompactor->compactRows( $rows ) );
			$results[$idx] = $rows;
			unset( $queries[$idx] );
		}

		if ( count( $queries ) !== 0 ) {
			// Log something about not finding everything?
		}

		return $results;
	}

	// Called prior to self::addToIndex only when new objects as inserted.  Gives the
	// opportunity for indexes to create rather than append if this object signifys a new
	// feature list.
	protected function maybeCreateIndex( array $indexed, array $sourceRow, array $compacted ) {
		return false;
	}

	// Since these affect the same $indexed bucket implementing classes can likely
	// do less round trips than this.
	protected function replaceInIndex( array $indexed, array $old, array $new ) {
		$this->removeFromIndex( $indexed, $old );
		$this->addToIndex( $indexed, $new );
	}

	protected function cacheKey( array $attributes ) {
		foreach( $attributes as $key => $attr ) {
			if ( $attr instanceof \Flow\Model\UUID ) {
				$attributes[$key] = $attr->getHex();
			} elseif ( strlen( $attr ) == 16 && preg_match( '/_id$/', $key ) ) {
				$uuid = new \Flow\Model\UUID( $attr );
				$attributes[$key] = $uuid->getHex();
			}
		}

		return wfForeignMemcKey( self::cachedDbId(), '', $this->prefix, implode( ':', $attributes ) );
	}

	/**
	 * @return string The id of the database being cached
	 */
	static public function cachedDbId() {
		global $wgFlowDefaultWikiDb;
		if ( $wgFlowDefaultWikiDb === false ) {
			return wfWikiId();
		} else {
			return $wgFlowDefaultWikiDb;
		}
	}
}

/**
 * Offers direct lookup of an object via a unique feature(set of properties)
 * on the object.
 */
class UniqueFeatureIndex extends FeatureIndex {

	public function getLimit() {
		return 1;
	}

	public function queryOptions() {
		return array( 'LIMIT' => 1 );
	}

	public function limitIndexSize( array $values ) {
		if ( count( $values ) > 1 ) {
			throw new \MWException( 'Unique index should never have more than 1 value' );
		}
		return $values;
	}

	protected function addToIndex( array $indexed, array $row ) {
		$this->cache->set( $this->cacheKey( $indexed ), array( $row ) );
	}

	protected function removeFromIndex( array $indexed, array $row ) {
		$this->cache->delete( $this->cacheKey( $indexed ) );
	}

	protected function replaceInIndex( array $indexed, array $oldRow, array $newRow ) {
		$this->cache->set( $this->cacheKey( $indexed ), array( $newRow ) );
	}
}

/**
 * Holds the top k items with matching $indexed columns.  List is sorted and truncated to specified size.
 */
class TopKIndex extends FeatureIndex {
	public function __construct( BufferedCache $cache, ObjectStorage $storage, $prefix, array $indexed, array $options = array() ) {
		if ( empty( $options['sort'] ) ) {
			throw new \InvalidArgumentException( 'TopKIndex must be sorted' );
		}

		parent::__construct( $cache, $storage, $prefix, $indexed );

		$this->options = $options + array(
			'limit' => 500,
			'order' => 'DESC',
			'create' => function() { return false; },
			'shallow' => null,
		);
		if ( !is_array( $this->options['sort'] ) ) {
			$this->options['sort'] = array( $this->options['sort'] );
		}
		if ( $this->options['shallow'] ) {
			// TODO: perhaps we shouldn't even get a shallow option, just receive a proper compactor in FeatureIndex::__construct
			$this->rowCompactor = new ShallowCompactor( $this->rowCompactor, $this->options['shallow'], $this->options['sort'] );
		}
	}

	public function canAnswer( array $keys, array $options ) {
		if ( !parent::canAnswer( $keys, $options ) ) {
			return false;
		}
		if ( isset( $options['sort'], $options['order'] ) ) {
			return $options['sort'] === $this->options['sort']
				&& $options['order'] === $this->options['order'];
		}
		return true;
	}

	public function getLimit() {
		return $this->options['limit'];
	}

	protected function maybeCreateIndex( array $indexed, array $sourceRow, array $compacted ) {
		if ( call_user_func( $this->options['create'], $sourceRow ) ) {
			$this->cache->set( $this->cacheKey( $indexed ), array( $compacted ) );
			return true;
		}
		return false;
	}

	protected function addToIndex( array $indexed, array $row ) {
		$self = $this;
		// If this used redis instead of memcached, could it add to index in position
		// without retry possibility? need a single number that will properly sort rows.
		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $self, $row ) {
				if ( $value === false ) {
					return false;
				}
				$idx = array_search( $row, $value );
				if ( $idx !== false ) {
					return false; // This row already exists somehow
				}
				$retval = $value;
				$retval[] = $row;
				$retval = $self->sortIndex( $retval );
				$retval = $self->limitIndexSize( $retval );
				if ( $retval === $value ) {
					// object didnt fit in index
					return false;
				} else {
					return $retval;
				}
			}
		);
	}

	protected function removeFromIndex( array $indexed, array $row ) {
		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $row ) {
				if ( $value === false ) {
					return false;
				}
				$idx = array_search( $row, $value );
				if ( $idx === false ) {
					return false;
				}
				unset( $value[$idx] );
				return $value;
			}
		);
	}

	protected function replaceInIndex( array $indexed, array $oldRow, array $newRow ) {
		$self = $this;
		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $self, $oldRow, $newRow ) {
				if ( $value === false ) {
					return false;
				}
				$retval = $value;
				$idx = array_search( $oldRow, $retval );
				if ( $idx !== false ) {
					unset( $retval[$idx] );
				}
				$retval[] = $newRow;
				$retval = $self->sortIndex( $retval );
				$retval = $self->limitIndexSize( $retval );
				if ( $value === $retval ) {
					// new item didnt fit in index and old item wasnt found in index
					return false;
				} else {
					return $value;
				}
			}
		);
	}

	// INTERNAL: in 5.4 it can be protected
	public function sortIndex( array $values ) {
		// I dont think this is a valid way to sort a 128bit integer string
		usort( $values, new SortArrayByKeys( $this->options['sort'], true ) );
		if ( $this->options['order'] === 'DESC' ) {
			$values = array_reverse( $values );
		}
		return $values;
	}

	// INTERNAL: in 5.4 it can be protected
	public function limitIndexSize( array $values ) {
		return array_slice( $values, 0, $this->options['limit'] );
	}

	// INTERNAL: in 5.4 it can be protected
	public function queryOptions() {
		$options = array( 'LIMIT' => $this->options['limit'] );

		$orderBy = array();
		$order = $this->options['order'];
		foreach ( $this->options['sort'] as $key ) {
			$orderBy[] = "$key $order";
		}
		$options['ORDER BY'] = $orderBy;

		return $options;
	}
}

/**
 * Removes the feature fields from stored array since its duplicating the cache key values
 * Re-adds them when retreiving from cache.
 */
class FeatureCompactor implements Compactor {
	public function __construct( array $indexedColumns ) {
		$this->indexed = $indexedColumns;
	}

	/**
	 * The indexed values are always available when querying, this strips
	 * the duplicated data.
	 */
	public function compactRow( array $row ) {
		foreach ( $this->indexed as $key ) {
			unset( $row[$key] );
		}
		foreach ( $row as $foo ) {
			if ( $foo !== null && !is_scalar( $foo ) ) {
				throw new \MWException( 'Attempted to compact row containing objects, must be scalar values: ' . print_r( $foo, true ) );
			}
		}
		return $row;
	}

	public function compactRows( array $rows ) {
		return array_map( array( $this, 'compactRow' ), $rows );
	}

	/**
	 * The $cached array is three dimensional.  Each top level key is a cache key
	 * and contains an array of rows.  Each row is an array representing a single data model.
	 *
	 * $cached = array( $cacheKey => array( array( 'rev_id' => 123, ... ), ... ), ... )
	 *
	 * The $keyToQuery array maps from cache key to the values that were used to build the cache key.
	 * These values are re-added to the results found in memcache.
	 *
	 * @param array $cached Array of results from BagOStuff::multiGet each containg a list of rows
	 * @param array $keyToQuery Map from key in $cached to the values used to generate that key
	 * @return array The $cached array with the queried values merged in
	 */
	public function expandCacheResult( array $cached, array $keyToQuery ) {
		foreach ( $cached as $key => $rows ) {
			$query = $keyToQuery[$key];
			foreach ( $query as $foo ) {
				if ( $foo !== null && !is_scalar( $foo ) ) {
					throw new \MWException( 'Query values to merge with cache contains objects, should be scalar values: ' . print_r( $foo, true ) );
				}
			}
			foreach ( $rows as $k => $row ) {
				foreach ( $row as $foo ) {
					if ( $foo !== null && !is_scalar( $foo ) ) {
						throw new \MWException( 'Result from cache contains objects, should be scalar values: ' . print_r( $foo, true ) );
					}
				}
				$cached[$key][$k] += $query;
			}
		}

		return $cached;
	}
}

/**
 * Backs an index with a UniqueFeatureIndex.  This index will store only the primary key
 * values from the unique index, and on retrieval from cache will materialize the primary key
 * values into full rows from the unique index.
 */
class ShallowCompactor implements Compactor {
	public function __construct( Compactor $inner, UniqueFeatureIndex $shallow, array $sortedColumns ) {
		$this->inner = $inner;
		$this->shallow = $shallow;
		$this->sort = $sortedColumns;
	}

	public function compactRow( array $row ) {
		$keys = array_merge( $this->shallow->getPrimaryKeyColumns(), $this->sort );
		$extra = array_diff( array_keys( $row ), $keys );
		foreach ( $extra as $key ) {
			unset( $row[$key] );
		}
		return $this->inner->compactRow( $row );
	}

	public function compactRows( array $rows ) {
		return array_map( array( $this, 'compactRow' ), $rows );
	}

	public function expandCacheResult( array $cached, array $keyToQuery ) {
		$results = $this->inner->expandCacheResult( $cached, $keyToQuery );
		// Allows us to flatten $results into a single $query array, then
		// rebuild final return value in same structure and order as $results.
		$duplicator = new ResultDuplicator( $this->shallow->getPrimaryKeyColumns(), 2 );
		foreach ( $results as $i => $rows ) {
			foreach ( $rows as $j => $row ) {
				$duplicator->add( $row, array( $i, $j ) );
			}
		}

		$innerResult = $this->shallow->findMulti( $duplicator->getUniqueQueries() );
		foreach ( $innerResult as $rows ) {
			// __construct guaranteed the shallow backing index is a unique, so $first is only result
			$first = reset( $rows );
			$duplicator->merge( $first, $first );
		}

		return $duplicator->getResult( /* strict = */ true );
	}
}

/**
 * Performs the equivilent of an SQL ORDER BY c1 ASC, c2 ASC...
 * Always sorts in ascending order.  array_reverse to get all descending.
 * For varied asc/desc needs implementation changes.
 *
 * usage: usort( $array, new SortArrayByKeys( array( 'c1', 'c2' ) ) );
 */
class SortArrayByKeys {
	protected $keys;
	protected $strict;

	public function __construct( array $keys, $strict = false ) {
		$this->keys = $keys;
		$this->strict = $strict;
	}

	public function __invoke( $a, $b ) {
		return self::compare( $a, $b, $this->keys, $this->strict );
	}

	static public function compare( $a, $b, $keys, $strict = false ) {
		$key = array_shift( $keys );
		if ( !isset( $a[$key] ) ) {
			return isset( $b[$key] ) ? -1 : 0;
		} elseif ( !isset( $b[$key] ) ) {
			return 1;
		} elseif ( $strict ? $a[$key] === $b[$key] : $a[$key] == $b[$key] ) {
			return $keys ? self::compare( $a, $b, $keys, $strict ) : 0;
		} else { // is there such a thing as strict gt/lt ?
			return $a[$key] > $b[$key] ? 1 : -1;
		}
	}
}

// Untested method of handling duplicate requests for the same data
// Preserves any BagOStuff semantics like BufferedCache does
class LocalBufferedCache extends BufferedCache {
	protected $internal = array();

	public function get( $key ) {
		if ( array_key_exists( $key, $this->internal ) ) {
			return $this->internal;
		}
		return $this->internal[$key] = parent::get( $key );
	}

	public function getMulti( array $keys ) {
		array_map( array( $this, 'ensureNotBinary' ), $keys );

		$found = array();
		foreach ( $keys as $idx => $key ) {
			if ( array_key_exists( $key, $this->internal ) ) {
				// BagOStuff::multiGet doesn't return the unfound keys
				if ( $this->internal[$key] !== false ) {
					$found[$key] = $this->internal[$key];
				}
				unset( $keys[$idx] );
			}
		}
		if ( $keys ) {
			$flipped = array_flip( $keys );
			$res = parent::getMulti( $keys );
			if ( $res === false ) {
				wfDebugLog( __CLASS__, __FUNCTION__ . ': Failure requesting data from memcache : ' . implode( ',', $keys ) );
				return $found;
			}
			foreach ( $res as $key => $value ) {
				$this->internal[$key] = $found[$key] = $value;
				unset( $keys[$flipped[$key]] );
			}
			// BagOStuff::multiGet doesn't return the unfound keys, but we cache the result
			foreach ( $keys as $key ) {
				$this->internal[$key] = false;
			}
		}
		return $found;
	}

	public function add( $key, $value, $exptime = 0 ) {
		$this->ensureNotBinary( $key );

		if ( $this->buffer === null ) {
			if ( $this->cache->add( $key, $value, $exptime ) ) {
				$this->internal[$key] = $value;
			}
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'value', 'exptime' ),
			);
			// speculative ... could cause a ton of bugs due to normal assumptions
			// how to do this reasonably?
			if ( !array_key_exists( $key, $this->internal ) || $this->internal[$key] === false ) {
				$this->internal[$key] = $value;
			}
		}
	}

	public function set( $key, $value, $exptime = 0 ) {
		parent::set( $key, $value, $exptime );
		$this->internal[$key] = $value;
	}

	/**
	 * How to cache merge?  Wrap the callback, but it wont know about failure.
	 *
	 *
	public function merge( $key, \Closure $callback, $exptime = 0, $attempts = 10 ) {

	}
	 */

}

// wraps the write methods of memcache into a buffer which can be flushed
//
class BufferedCache {
	protected $cache;
	protected $buffer;

	public function __construct( BagOStuff $cache ) {
		$this->cache = $cache;
	}

	public function get( $key ) {
		$this->ensureNotBinary( $key );

		return $this->cache->get( $key );
	}

	public function getMulti( array $keys ) {
		array_map( array( $this, 'ensureNotBinary' ), $keys );
		return $this->cache->getMulti( $keys );
	}

	public function add( $key, $value, $exptime = 0 ) {
		$this->ensureNotBinary( $key );
		if ( $this->buffer === null ) {
			$this->cache->add( $key, $value, $exptime );
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'value', 'exptime' ),
			);
		}
	}

	public function set( $key, $value, $exptime = 0 ) {
		$this->ensureNotBinary( $key );
		if ( $this->buffer === null ) {
			$this->cache->set( $key, $value, $exptime );
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'value', 'exptime' )
			);
		}
	}

	public function delete( $key, $time = 0 ) {
		$this->ensureNotBinary( $key );
		if ( $this->buffer === null ) {
			$this->cache->delete( $key, $time );
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'time' ),
			);
		}
	}

	public function merge( $key, \Closure $callback, $exptime = 0, $attempts = 10 ) {
		$this->ensureNotBinary( $key );
		if ( $this->buffer === null ) {
			$this->cache->merge( $key, $callback, $exptime, $attempts );
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'callback', 'exptime', 'attempts' ),
			);
		}
	}

	public function begin() {
		if ( $this->buffer === null ) {
			$this->buffer = array();
		} else {
			throw new \MWException( 'Transaction already in progress' );
		}
	}

	public function commit() {
		if ( $this->buffer === null ) {
			throw new \MWException( 'No transaction in progress' );
		}
		foreach ( $this->buffer as $row ) {
			call_user_func_array(
				array( $this->cache, $row['command'] ),
				$row['arguments']
			);
		}
		$this->buffer = null;
	}

	public function rollback() {
		if ( $this->buffer === null ) {
			throw new \MWException( 'No transaction in progress' );
		}
		$this->buffer = null;
	}

	protected function ensureNotBinary( $key ) {
		if ( !ctype_print( $key ) ) {
			throw new \MWException( "Cache keys must be plain strings, provided: $key" );
		}
	}
}
