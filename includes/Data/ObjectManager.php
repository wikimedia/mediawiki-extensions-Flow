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
	 * The BagOStuff interface returns with keys matching the key, unfortunatly
	 * we deal with composite keys which makes that awkward. Instead all
	 * findMulti implementations return their data with the same array indexes
	 * as the query in $queries. The order the requested queries are returned in
	 * is arbitrary.
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
interface WritableObjectStorage extends ObjectStorage {
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
	 * Convert a db row to its domain model.
	 */
	function fromStorageRow( array $row );
}

// An Index is just a store that receives updates via handler.
// backing store's can be passed via constructor
interface Index extends LifecycleHandler {
	// Indexes accept no query options
	function find( array $keys );

	// Indexes accept no query options
	function findMulti( array $queries );

	// Maximum number of items in a single index value
	function getLimit();

	// Can the index locate a result for this keys and options pair
	function canAnswer( array $keys, array $options );
}

// A little glue code so you dont need to use the individual manager for each class
// Can be made more specific once the interfaces settle down
class ManagerGroup {
	public function __construct( Container $container, array $classMap ) {
		$this->container = $container;
		$this->classMap = $classMap;
	}

	public function getStorage( $className ) {
		if ( !isset( $this->classMap[$className] ) ) {
			throw new \Exception( "Request for '$className' is not in classmap: " . implode( ', ', array_keys( $this->classMap ) ) );
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
		throw new \Exception( 'Not Implemented' );
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
		if ( isset( $options['limit'] ) ) {
			$limit = $options['limit'];
			$offset = isset( $options['offset'] ) ? $options['offset'] : 0;
		} else {
			$limit = false;
		}

		$res = $this->getIndexFor( $keys, $options )->findMulti( $queries );
		if ( $res === null ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $i => $rows ) {
			if ( $limit ) {
				$rows = array_slice( $rows, $offset, $limit, true );
			}
			foreach ( $rows as $j => $row ) {
				$retval[$i][$j] = $this->load( $row );
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

	protected function getIndexFor( array $keys, array $options = array() ) {
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
			throw new \Exception(
				'No index available to answer query for ' . implode( ', ', $keys ) .
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
 * Cant implement WritableObjectStorage because 'update' method signature differs
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
			$new = $this->mapper->toStorageRow( $object );
			$this->storage->insert( $new );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterInsert( $object, $new );
			}
			$this->loaded[$object] = $new;
		} catch ( \Exception $e ) {
			throw new PersistenceException( 'failed insert', null, $e );
		}
	}

	protected function update( $object ) {
		try {
			$old = $this->loaded[$object];
			$new = $this->mapper->toStorageRow( $object );
			foreach ( $new as $k => $x ) {
				if ( $x !== null && !is_scalar( $x ) ) {
					throw new \RuntimeException( "Expected mapper to return all scalars, but '$k' is " . gettype( $x ) );
				}
			}
			if ( self::arrayEquals( $old, $new ) ) {
				return;
			}
			$this->storage->update( $old, $new );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterUpdate( $object, $old, $new );
			}
			$this->loaded[$object] = $new;
		} catch ( \Exception $e ) {
			throw new PersistenceException( 'failed update', null, $e );
		}
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

	public function remove( $object ) {
		try {
			$old = $this->loaded[$object];
			$this->storage->remove( $old );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onAfterRemove( $object, $old );
			}
			unset( $this->loaded[$object] );
		} catch ( \Exception $e ) {
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

	public function multiPut( array $objects ) {
		throw new \Exception( 'Not Implemented' );
	}

	public function multiDelete( array $objects ) {
		throw new \Exception( 'Not Implemented' );
	}
}
class PersistenceException extends \Exception {
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
	public function fromStorageRow( array $row ) {
		return call_user_func( $this->fromStorageRow, $row );
	}
}

// Doesn't support updating primary key value yet
class BasicDbStorage implements WritableObjectStorage {
	public function __construct( DbFactory $dbFactory, $table, array $primaryKey ) {
		if ( !$primaryKey ) {
			throw new \Exception( 'PK required' );
		}
		$this->dbFactory = $dbFactory;
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}

	public function insert( array $row ) {
		// insert returns boolean true/false
		return $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->table,
			$row,
			__METHOD__
		);
	}

	public function update( array $old, array $new ) {
		$pk = ObjectManager::splitFromRow( $old, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $old ) );
			throw new PersistenceException( 'Row has null primary key: ' . implode( $missing ) );
		}
		$updates = $this->calcUpdates( $old, $new );
		if ( !$updates ) {
			return true; // nothing to change, success
		}
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		// update returns boolean true/false as $res
		$res = $dbw->update( $this->table, $updates, UUID::convertUUIDs( $pk ), __METHOD__ );
		// $dbw->update returns boolean true/false as $res
		// we also want to check that $pk actually selected a row to update
		return $res && $dbw->affectedRows();
	}

	protected function calcUpdates( array $old, array $new ) {
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
	 * @return boolean success
	 */
	public function remove( array $row ) {
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $row ) );
			throw new PersistenceException( 'Row has null primary key: ' . implode( $missing ) );
		}
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->delete( $this->table, UUID::convertUUIDs( $pk ), __METHOD__ );
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

		$res = $this->dbFactory->getDB( DB_MASTER )->select(
			$this->table,
			'*',
			UUID::convertUUIDs( $attributes ),
			__METHOD__,
			$options
		);
		if ( ! $res ) {
			return null;
		}
		$result = array();
		foreach ( $res as $row ) {
			$result[] = (array) $row;
		}
		wfDebugLog( __CLASS__, __METHOD__ . ': ' . print_r( $result, true ) );
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
}

/**
 * Index objects with equal features($indexedColumns) into the same buckets.
 */
abstract class FeatureIndex implements Index {

	abstract public function getLimit();
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

	public function canAnswer( array $keys, array $options ) {
		sort( $keys );
		if ( $keys !== $this->indexedOrdered ) {
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

	public function onAfterInsert( $object, array $new) {
		$indexed = ObjectManager::splitFromRow( $new , $this->indexed );
		// is un-indexable a bail-worthy occasion? Probably not
		if ( !$indexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $new ) );
		}
		$this->addToIndex( $indexed, $this->compactRow( $new ) );
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$oldIndexed = ObjectManager::splitFromRow( $old, $this->indexed );
		$newIndexed = ObjectManager::splitFromRow( $new, $this->indexed );
		// TODO: optimize $oldIndexed === $newIndexed, which should be common
		// TODO: optimize out occurances where data changed but not the values
		//       indexed or stored in the index(for shallow indexes)
		if ( !$oldIndexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $oldIndexed ) );
		}
		if ( !$newIndexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $newIndexed ) );
		}
		$this->removeFromIndex( $oldIndexed, $this->compactRow( $old ) );
		$this->addToIndex( $newIndexed, $this->compactRow( $new ) );
	}

	public function onAfterRemove( $object, array $old ) {
		$indexed = ObjectManager::splitFromRow( $old, $this->indexed );
		if ( !$indexed ) {
			throw new \MWException( 'Unindexable row: ' .json_encode( $old ) );
		}
		$this->removeFromIndex( $indexed, $this->compactRow( $old ) );
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
			if ( !isset( $keyToQuery[$key] ) ) {
				$idxToKey[$idx] = $key;
				$keyToQuery[$key] = UUID::convertUUIDs( $query );
			}
		}

		// Retreive from cache
		$cached = $this->cache->getMulti( array_keys( $keyToIdx ) );
		// expand partial results
		foreach( $this->expandCacheResult( $cached, $keyToQuery ) as $key => $rows ) {
			foreach ( $keyToIdx[$key] as $idx ) {
				$results[$idx] = $rows;
				unset( $queries[$idx] );
			}
		}
		// dont need to query backing store
		if ( count( $queries ) === 0 ) {
			return $results;
		}
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
						throw new \Exception( "Received non-scalar row value for '$k' from: " . get_class( $this->storage ) );
					}
				}
			}
			$this->cache->add( $idxToKey[$idx], $this->compactRows( $rows ) );
			$results[$idx] = $rows;
			unset( $queries[$idx] );
		}

		if ( count( $queries ) !== 0 ) {
			// Log something about not finding everything?
		}

		return $results;
	}


	protected function cacheKey( array $attributes ) {
		global $wgFlowDefaultWikiDb; // meh
		if ( $wgFlowDefaultWikiDb === false ) {
			$db = wfWikiId();
		} else {
			$db = $wgFlowDefaultWikiDb;
		}

		foreach( $attributes as $key => $attr ) {
			if ( $attr instanceof \Flow\Model\UUID ) {
				$attributes[$key] = $attr->getHex();
			} elseif ( strlen($attr) == 16 && preg_match( '/_id$/', $key ) ) {
				$uuid = new \Flow\Model\UUID( $attr );
				$attributes[$key] = $uuid->getHex();
			}
		}

		return wfForeignMemcKey( $db, '', $this->prefix, implode( ':', $attributes ) );
	}

	/**
	 * The indexed values are always available when querying, this strips
	 * the duplicated data.
	 */
	protected function compactRow( array $row ) {
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

	protected function compactRows( array $rows ) {
		$compacted = array();
		foreach ( $rows as $key => $row ) {
			$compacted[$key] = $this->compactRow( $row );
		}
		return $compacted;
	}

	// Each item in $cached is a result from self::compactRows
	// All at once to allow bulking any IO implementing classes may want to do
	protected function expandCacheResult( array $cached, array $keyToQuery ) {
		foreach ( $cached as $key => $rows ) {
			$expanded = array();
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
				$cached[$key][$k] = $row + $query;
			}
		}

		return $cached;
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
}

/**
 * Holds the top k items with matching $indexed columns.  List is sorted and truncated to specified size.
 */
class TopKIndex extends FeatureIndex {
	public function __construct( BufferedCache $cache, ObjectStorage $storage, $prefix, array $indexed, array $options = array() ) {
		if ( empty( $options['sort'] ) ) {
			throw new \InvalidArgumentException( 'TopKIndex must be sorted' );
		}
		if ( isset( $options['shallow'] ) && !$options['shallow'] instanceof UniqueFeatureIndex ) {
			throw new \InvalidArgumentException( "The 'shallow' option must be either null or UniqueFeatureIndex." );
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
	}

	public function getLimit() {
		return $this->options['limit'];
	}

	protected function addToIndex( array $indexed, array $row ) {
		$cacheKey = $this->cacheKey( $indexed );
		if ( call_user_func( $this->options['create'], $indexed + $row ) ) {
			$this->cache->set( $cacheKey, array( $row ) );
			return;
		}
		$self = $this;
		// If this used redis instead of memcached, could it add to index in position
		// without retry possibility? need a single number that will properly sort rows.
		$this->cache->merge(
			$cacheKey,
			function( BagOStuff $cache, $key, $value ) use( $self, $row ) {
				if ( $value === false ) {
					return false;
				}
				$idx = array_search( $row, $value );
				if ( $idx !== false ) {
					return false; // This row already exists somehow
				}
				$value[] = $row;
				$value = $self->sortIndex( $value );
				return $self->limitIndexSize( $value );
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

	protected function compactRow( array $row ) {
		// An OO solution wouldn't check this, it would have a compact/expand
		// implementation
		if ( isset( $this->options['shallow'] ) ) {
			$keys = array_merge(
				$this->options['shallow']->getPrimaryKeyColumns(),
				$this->options['sort']
			);
			$extra = array_diff( array_keys( $row ), $keys );
			foreach ( $extra as $key ) {
				unset( $row[$key] );
			}
		}
		return parent::compactRow( $row );
	}


	protected function expandCacheResult( array $cached, array $keyToQuery ) {
		$cached = parent::expandCacheResult( $cached, $keyToQuery );
		if ( $cached && $this->options['shallow'] ) {
			return $this->expandShallowResult( $cached );
		}
		return $cached;
	}

	protected function expandShallowResult( array $results ) {
		// Allows us to flatten $results into a single $query array, then
		// rebuild final return value in same structure and order as $results.
		$duplicator = new ResultDuplicator( $this->options['shallow']->getPrimaryKeyColumns(), 2 );
		foreach ( $results as $i => $rows ) {
			foreach ( $rows as $j => $row ) {
				$duplicator->add( $row, array( $i, $j ) );
			}
		}

		$innerResult = $this->options['shallow']->findMulti( $duplicator->getUniqueQueries() );
		foreach ( $innerResult as $rows ) {
			// __construct guaranteed the shallow backing index is a unique, so $first is only result
			$first = reset( $rows );
			$duplicator->merge( $first, $first );
		}

		// TODO: fix whatever bug doesnt allow this to be strict
		return $duplicator->getResult();
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

	public function setStrict() {
		$this->strict = true;
		return $this;
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
class LocalBufferedCache extends BufferedCache {
	protected $internal = array();

	public function get( $key ) {
		if ( array_key_exists( $key, $this->internal ) ) {
			return $this->internal;
		}
		return $this->internal[$key] = parent::get( $key );
	}

	public function getMulti( array $keys ) {
		$found = array();
		foreach ( $keys as $idx => $key ) {
			if ( array_key_exists( $key, $this->internal ) ) {
				$found[$key] = $this->internal[$key];
				unset( $keys[$idx] );
			}
		}
		if ( $keys ) {
			$flipped = array_flip( $keys );
			foreach ( parent::getMulti( $keys ) as $key => $value ) {
				$this->internal[$key] = $found[$key] = $value;
			}
		}
		return $found;
	}

	public function add( $key, $value, $exptime = 0 ) {
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
			if ( !isset( $this->internal[$key] ) ) {
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
		return $this->cache->get( $key );
	}

	public function getMulti( array $keys ) {
		return $this->cache->getMulti( $keys );
	}

	public function add( $key, $value, $exptime = 0 ) {
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
}
