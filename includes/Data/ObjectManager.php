<?php

namespace Flow\Data;

use Flow\Container;
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
// Different indexes on the same ObjectManager will always use the same storage

// TODO: This interface is too tied to the ObjectManager,  it should
// be possible to use things implementing LifecycleHandler without
// an ObjectManager instances(to keep things simpler).
interface LifecycleHandler {
	function onPostLoad( ObjectLocator $om, $object, $row );
	function onPostInsert( ObjectManager $om, $object, $row );
	function onPostUpdate( ObjectManager $om, $object, $row );
	function onPostRemove( ObjectManager $om, $object, $row );
}

// Some denormalized data doesnt accept writes, it merely triggers cache updates
// when something else does the write.
interface ObjectStorage {
	function find( array $attributes, array $options = array() );
	function findMulti( array $queries, array $options = array() );
	function getPrimaryKeyColumns();
}

interface WritableObjectStorage extends ObjectStorage {
	function insert( array $row );
	function update( array $row, array $changeSet );
	function remove( array $row );
}

// Perhaps better names, because this isnt php serialization, its domain model <-> db row
interface ObjectMapper {
	/**
	 * Convert $object from the domain model to its db row
	 */
	function serialize( ObjectManager $om, $object );

	/**
	 * Convert a db row to its domain model.
	 */
	function unserialize( ObjectManager $om, $row );
}

// An Index is just a store that receives updates via handler.
// backing store's can be passed via constructor
interface Index extends LifecycleHandler, ObjectStorage {
	// Maximum number of items in a single index value
	function getLimit();
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
 *
 * Error handling is all wrong, but simplifies prototyping.
 */
class ObjectLocator {
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

	/**
	 * All queries must be against the same index
	 *
	 * @return array|null  null is query failure.  empty array is no result.  array is success
	 */
	public function findMulti( array $queries, array $options = array() ) {
		if ( !$queries ) {
			return array();
		}
		$keys = array_keys( reset( $queries ) );
		$result = $this->multiQueryIndex( $this->getIndexFor( $keys, $options ), $queries, $options );
		// index returns whatever it has, regardless of the limit
		if ( isset( $options['limit'] ) ) {
			foreach ( $result as $idx => $data ) {
				$result[$idx] = array_slice( $data, 0, $options['limit'] );
			}
		}
		return $result;
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
		$pk = $this->storage->getPrimaryKeyColumns();
		$queries = array();
		foreach ( $objectIds as $id ) {
			$queries[] = array_combine( $pk, (array) $id );
		}
		// primary key is unique, but indexes still return their results as array
		// to be consistent. undo that for a flat result array
		$retval = array();
		foreach ( $this->findMulti( $queries ) as $row ) {
			$retval[] = reset( $row );
		}
		return $retval;
	}

	/**
	 * Only use from maintenance.  Updates not implemented(yet), needs
	 * handling for transactions.
	 */
	public function visitAll( $callback ) {
		// storage is IteratorAggregate returning EchoBatchRowIterator of
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
		$limit = isset( $options['limit'] ) ? $options['limit'] : null;
		$sort = isset( $options['sort'] ) ? $options['sort'] : null;
		if ( $sort !== null && !is_array( $sort ) ) {
			$sort = (array) $sort;
		}

		// depends on indexes returning keys pre-sorted
		// perhaps instead, $index->canAnswer( $keys, $options );
		foreach ( $this->indexes as $index ) {
			if ( !$index->canAnswer( $keys, $options ) ) {
				continue;
			}
			if ( $limit === null ) {
				return $index;
			}
			if ( $limit > $index->getLimit() ) {
				continue;
			}
			if ( $current !== null && $current->getLimit() <= $index->getLimit() ) {
				continue;
			}
			$current = $index;
		}
		if ( $current === null ) {
			throw new \Exception(
				'No index available to answer query for ' . implode( ', ', $keys ) .
				' with options ' . json_encode( $options )
			);
		}
		return $current;
	}

	protected function multiQueryIndex( Index $index, array $queries, array $options = array() ) {
		$res = $index->findMulti( $queries, $options );
		if ( !$res ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $key => $rows ) {
			$tmp = array();
			foreach ( $rows as $row ) {
				$tmp[] = $this->load( $row );
			}
			$retval[$key] = $tmp;
		}
		return $retval;
	}

	protected function load( $row ) {
		$object = $this->mapper->unserialize( $this, $row );
		foreach ( $this->lifecycleHandlers as $handler ) {
			$handler->onPostLoad( $this, $object, $row );
		}
		return $object;
	}
}

/**
 * Writable indexes. Error handling is all wrong currently.
 */
class ObjectManager extends ObjectLocator {
	// @var SplObjectHash $loaded If the object exists then an 'update' is issued, otherwise 'insert'
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
			$this->loaded[$object] = $this->mapper->serialize( $object );
		}
	}

	protected function insert( $object ) {
		try {
			$row = $this->mapper->serialize( $this, $object );
			$this->storage->insert( $row );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onPostInsert( $this, $object, $row );
			}
			$this->loaded[$object] = $row;
		} catch ( \Exception $e ) {
			throw $e;
			throw new PersistenceException( 'failed insert' );
		}
	}

	protected function update( $object ) {
		try {
			$row = $this->mapper->serialize( $this, $object );
			$changeSet = $this->getChangeSet( $object, $row );
			if ( !$changeSet ) {
				return;
			}
			$this->storage->update( $row, $changeSet );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onPostUpdate( $this, $object, $row );
			}
			$this->loaded[$object] = $row;
		} catch ( \Exception $e ) {
			throw $e;
			throw new PersistenceException( 'failed update' );
		}
	}

	public function remove( $object ) {
		try {
			$row = $this->mapper->serialize( $this, $object );
			$this->storage->remove( $row );
			foreach ( $this->lifecycleHandlers as $handler ) {
				$handler->onPostRemove( $this, $object, $row );
			}
			unset( $this->loaded[$object] );
		} catch ( \Exception $e ) {
			throw $e;
			throw new PersistenceException( 'failed remove' );
		}
	}

	public function clear() {
		unset( $this->loaded ); // unset before recreate allows php to GC then reuse same memory
		$this->loaded = new SplObjectHash;
	}

	protected function load( $row ) {
		$object = parent::load( $row );
		$this->loaded[$object] = $row;
		return $object;
	}

	/**
	 * Indexes need to know what changed, this lets them find out
	 * @param mixed $object The object to calculate change set against. Used to fetch
	 *     loaded state from internal cache.
	 * @param array $row The new state of the object
	 * @param array|null $keys Only calculate changeset for provided keys
	 * @return array
	 */
	public function getChangeSet( $object, $row, $keys = null ) {
		if ( isset( $this->loaded[$object] ) ) {
			$loaded = $this->loaded[$object];
		} else {
			$loaded = array();
		}
		$old = array_keys( $loaded );
		$new = array_keys( $row );
		if ( $keys !== null ) {
			$old = array_intersect( $keys, $old );
			$new = array_intersect( $keys, $new );
		}
		$changes = array();
		foreach ( array_intersect( $old, $new ) as $key ) {
			if ( $loaded[$key] !== $row[$key] ) {
				$changes[$key] = array(
					'old' => $loaded[$key],
					'new' => $row[$key],
				);
			}
		}
		foreach ( array_diff( $old, $new ) as $key ) {
			$changes[$key] = array(
				'old' => $loaded[$key],
				'new' => null,
			);
		}

		foreach ( array_diff( $new, $old ) as $key ) {
			$changes[$key] = array(
				'old' => null,
				'new' => $row[$key],
			);
		}
		return $changes;
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
 *     array( 'User', 'newFromRow' ),
 * );
 */
class BasicObjectMapper implements ObjectMapper {
	public function __construct( $serialize, $unserialize ) {
		$this->serialize = $serialize;
		$this->unserialize = $unserialize;
	}

	static public function model( $className ) {
		return new self( array( $className, 'toStorageRow' ), array( $className, 'loadFromRow' ) );
	}

	public function serialize( ObjectManager $om, $object ) {
		return call_user_func( $this->serialize, $object );
	}
	public function unserialize( ObjectManager $om, $row ) {
		return call_user_func( $this->unserialize, $row );
	}
}

class BasicDbStorage implements WritableObjectStorage, \IteratorAggregate {
	public function __construct( DbFactory $dbFactory, $table, array $primaryKey ) {
		if ( empty( $primaryKey ) ) {
			throw new \Exception( 'PK required' );
		}
		$this->dbFactory = $dbFactory;
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}

	public function insert( array $row ) {
		// insert returns boolean true/false
		return $this->dbFactory->getDB( DB_MASTER )->insert( $this->table, $row, __METHOD__ );
	}

	public function update( array $row, array $changeSet ) {
		list( $pk, $values ) = $this->splitPkFromRow( $row );
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->update(
			$this->table,
			$values,
			$pk,
			__METHOD__
		);
		// update returns boolean true/false as $res
		// we also want to check that $pk actually selected a row to update
		return $res && $dbw->affectedRows();
	}

	/**
	 * @return boolean success
	 */
	public function remove( array $row ) {
		list( $pk, $_ ) = $this->splitPkFromRow( $row );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $row ) );
			throw new PersistenceException( 'Row has null primary key: ' . implode( $missing ) );
		}
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->delete( $this->table, $pk, __METHOD__ );
		return $res && $dbw->affectedRows();
	}

	/*
	 * @return array|null  Empty array means no result,  null means query failure.  Array with results is
	 *                     success.
	 */
	public function find( array $attributes, array $options = array() ) {
		$res = $this->dbFactory->getDB( DB_MASTER )->select( $this->table, '*', $attributes, __METHOD__, $options );
		if ( ! $res ) {
			return null;
		}
		$result = array();
		foreach ( $res as $row ) {
			$result[] = (array) $row;
		}
		return $result;
	}

	public function findMulti( array $queries, array $options = array() ) {
		// TODO: for items with single key to query an IN condition would be better
		$results = array();
		$dbr = $this->dbFactory->getDB( DB_MASTER );
		foreach ( $queries as $attributes ) {
			$conditions[] = $dbr->makeList( $attributes, LIST_AND );
		}
		$res = $dbr->select( $this->table, '*', $dbr->makeList( $conditions, LIST_OR ), __METHOD__, $option );
		if ( !$res ) {
			return null;
		}
		$result = array();
		foreach ( $res as $row ) {
			$result[] = (array) $row;
		}
		return $result;
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

	public function splitPkFromRow( array $row ) {
		foreach ( $this->primaryKey as $key ) {
			if ( !isset( $row[$key] ) ) {
				return array( null, $row );
			}
			$pk[$key] = $row[$key];
			unset( $row[$key] );
		}

		return array( $pk, $row );
	}
}

class NullLifecycleHandler implements LifecycleHandler {
	function onPostInsert( ObjectManager $om, $object, $row ) {
	}
	function onPostUpdate( ObjectManager $om, $object, $row ) {
	}
	function onPostLoad( ObjectLocator  $om, $object, $row ) {
	}
	function onPostRemove( ObjectManager $om, $object, $row ) {
	}
}

class UniqueIndex extends NullLifecycleHandler implements Index {

	public function __construct( BufferedCache $cache, ObjectStorage $storage, $prefix, array $indexedColumns ) {
		$this->cache = $cache;
		$this->storage = $storage;
		$this->prefix = $prefix;
		$this->indexed = $indexedColumns;
		// sort this and ksort is self::cacheKey to always have cache key
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

	public function getLimit() {
		return 1;
	}

	public function canAnswer( array $keys, array $options ) {
		sort( $keys );
		if ( $keys !== $this->indexedOrdered ) {
			return false;
		}
		// Unique can answer all options, since there is only one answer per key
		return true;
	}

	public function find( array $attributes, array $options = array() ) {
		$results = $this->findMulti( array( $attributes ), $options );
		return reset( $results );
	}

	// NOTE: doesnt use $options at all, instead query options are all pre-set
	// in the constructor.
	public function findMulti( array $queries, array $options = array() ) {
		if ( !$queries ) {
			return array();
		}
		// Check preconditions, ensure we can answer this request
		//
		foreach ( array_keys( $queries ) as $idx ) {
			ksort( $queries[$idx] );
			if ( array_keys( $queries[$idx] ) !== $this->indexedOrdered ) {
				throw new \MWException(
					'Cannot answer query for columns: ' . implode( ', ', array_keys( $queries[$idx] ) )
				);
			}
		}

		// Split the queries into cacheable and uncacheable
		$results = $requests = $need = $uncacheable = array();
		foreach ( $queries as $idx => $attributes ) {
			if ( $this->isCacheable( $attributes ) ) {
				$requests[$this->cacheKey( $attributes )] = $idx;
			} else {
				$uncacheable[] = $attributes;
			}
		}

		// Request the cached answers
		if ( $requests ) {
			$cached = $this->cache->getMulti( array_keys( $requests ) );
			foreach ( $requests as $cacheKey => $idx ) {
				if ( array_key_exists( $cacheKey, $cached ) ) {
					$results[$idx] = $cached[$cacheKey];
				} else {
					$need[] = $queries[$idx];
				}
			}
		}

		// Request any unfound or uncacheable queries
		// TODO: do uncacheable and need at same time.  uncacheable
		// should be fairly rare, i wouldn't worry about it for now
		if ( $uncacheable ) {
			$results = array_merge( $results, $this->storage->findMulti( $uncacheable, $this->queryOptions() ) );
		}

		if ( $need ) {
			$storeResult = $this->storage->findMulti( $need, $this->queryOptions() );
			$results = array_merge( $storeResult, $results );
			// idx => cacheKey
			$cacheKeys = array_flip( $requests );
			foreach ( $storeResult as $idx => $data ) {
				$this->cache->add( $cacheKeys[$idx], $this->limitIndexSize( $data ) );
			}
		}

		return $results;
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

	public function onPostInsert( ObjectManager $om, $object, $row ) {
		list( $old, $new ) = $this->getOldAndNewIndexed( $om, $object, $row );
		$this->addToIndex( $om, $row, $new );
	}

	public function onPostUpdate( ObjectManager $om, $object, $row ) {
		list( $old, $new ) = $this->getOldAndNewIndexed( $om, $object, $row );
		if ( $old !== $new ) {
			$this->removeFromIndex( $om, $row, $old );
			$this->addToIndex( $om, $row, $new, false );
		} else {
			$this->addToIndex( $om, $row, $new, true );
		}
	}

	public function onPostRemove( ObjectMAnager $om, $object, $row ) {
		list( $old ) = $this->getOldAndNewIndexed( $om, $object, array() );
		$this->removeFromIndex( $om, $row, $old );
	}

	protected function getOldAndNewIndexed( ObjectManager $om, $object, $row ) {
		$changeSet = $om->getChangeSet( $object, $row, $this->indexed );
		$old = $new = array();
		foreach ( $changeSet as $key => $changes ) {
			$old[$key] = $changes['old'];
			$new[$key] = $changes['new'];
		}

		return array( $old, $new );
	}

	protected function addToIndex( ObjectManager $om, $row, $indexed ) {
		if ( $this->isCacheable( $indexed ) ) {
			$this->cache->set( $this->cacheKey( $indexed ), array( $row ) );
		}
	}

	protected function removeFromIndex( ObjectManager $om, $row, $indexed ) {
		if ( $this->isCacheable( $indexed ) ) {
			$this->cache->delete( $this->cacheKey( $indexed ) );
		}
	}

	// How to cache something with null index keys?
	protected function isCacheable( array $attributes ) {
		foreach ( $attributes as $value ) {
			if ( $value === null ) {
				return false;
			}
		}
		return true;
	}

	protected function cacheKey( array $attributes ) {
		return wfForeignMemcKey( 'flow', '', $this->prefix, implode( ':', $attributes ) );
	}
}

class SecondaryIndex extends UniqueIndex {
	public function __construct( BufferedCache $cache, ObjectStorage $storage, $prefix, array $indexedColumns, array $options = array() ) {
		parent::__construct( $cache, $storage, $prefix, $indexedColumns );
		$this->options = $options + array(
			// How big is 500?  if using a shallow index with a single 6 char
			// key mapping to 39 char id,  about 35k. After gzdeflate 8-10kB.
			//
			// we could optimize out the keys, but the  repetitive nature of them
			// is trivial for gzdeflate, with a 500 limit storing just id, rather than array
			// of key=>id saves only 5%, or 400 bytes. More benefit would come from packing
			// the ids into bytes, but thats a bit overboad vs builtin client compression.
			'limit' => 500,
			'order' => 'DESC',
			'sort' => $indexedColumns,
			'create' => function() { return false; },
		);
		if ( !is_array( $this->options['sort'] ) ) {
			$this->options['sort'] = array( $this->options['sort'] );
		}
	}

	public function canAnswer( array $keys, array $options ) {
		if ( !parent::canAnswer( $keys, $options ) ) {
			return false;
		}
		if ( isset( $options['sort'] ) ) {
			if ( (array) $options['sort'] !== $this->options['sort'] ) {
				return false;
			}
			if ( $options['order'] !== $this->options['order'] ) {
				return false;
			}
		}
		return true;
	}

	public function getLimit() {
		return $this->options['limit'];
	}

	protected function addToIndex( ObjectManager $om, $row, $indexed, $overwrite = true ) {
		if ( !$this->isCacheable( $indexed ) ) {
			return;
		}
		if ( call_user_func( $this->options['create'], $row ) ) {
			//echo "Create secondary index\n";
			$this->cache->set( $this->cacheKey( $indexed ), array( $row ) );
			return;
		}
		$self = $this;
		// If this used redis instead of memcached, could it add to index in position
		// without retry possibility?
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
				$value[] = $row;
				$value = $self->sortIndex( $value );
				return $self->limitIndexSize( $value );
			}
		);
	}

	protected function removeFromIndex( ObjectManager $om, $row, $indexed, $overwrite = false ) {
		if ( !$this->isCacheable( $indexed ) ) {
			return;
		}
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
		usort( $values, new SortArrayByKeys( (array) $this->options['sort'], true ) );
		if ( $this->options['order'] === 'DESC' ) {
			echo "Reverse array\n";
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
		$options['LIMIT'] = $this->options['limit'];

		if ( isset( $this->options['sort'] ) && $this->options['sort'] ) {
			$orderBy = array();
			$order = $this->options['order'];
			foreach ( $this->options['sort'] as $key ) {
				$orderBy[] = "$key $order";
			}
			$options['ORDER BY'] = $orderBy;
		}

		return $options;
	}
}

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
				$found[$key] = $value;
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
		//echo __METHOD__, " $key\n";
		return $this->cache->get( $key );
	}

	public function getMulti( array $keys ) {
		return $this->cache->getMulti( $keys );
	}
	public function add( $key, $value, $exptime = 0 ) {
		//echo __METHOD__, " $key : $value\n";
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
		//echo __METHOD__, " $key : $value\n";
		if ( $this->buffer === null ) {
			$this->cache->set( $key, $value, $exptime );
		} else {
			$this->buffer[] = array(
				'command' => __FUNCTION__,
				'arguments' => compact( 'key', 'value', 'exptime' )
			);
		}
	}

	public function merge( $key, \Closure $callback, $exptime = 0, $attempts = 10 ) {
		//echo __METHOD__, " : $key\n";
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
