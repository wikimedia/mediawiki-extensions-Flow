<?php

namespace Flow\Data;

use FormatJson;
use Flow\Exception\NoIndexException;
use Flow\Model\UUID;

/**
 * Denormalized indexes that are query-only.  The indexes used here must
 * be provided to some ObjectManager as a lifecycleHandler to receive
 * update events.
 * Error handling is all wrong, but simplifies prototyping.
 */
class ObjectLocator {
	/*
	 * @var ObjectMapper
	 */
	protected $mapper;

	/**
	 * @var ObjectStorage
	 */
	protected $storage;

	/**
	 * @var Index[]
	 */
	protected $indexes;

	/**
	 * @var LifecycleHandler[]
	 */
	protected $lifecycleHandlers;

	public function __construct( ObjectMapper $mapper, ObjectStorage $storage, array $indexes = array(), array $lifecycleHandlers = array() ) {
		$this->mapper = $mapper;
		$this->storage = $storage;
		$this->indexes = $indexes;
		$this->lifecycleHandlers = array_merge( $indexes, $lifecycleHandlers );
	}

	public function getMapper() {
		return $this->mapper;
	}

	public function find( array $attributes, array $options = array() ) {
		$result = $this->findMulti( array( $attributes ), $options );
		return $result ? reset( $result ) : null;
	}

	public function getIterator() {
		return $this->storage->getIterator();
	}

	/**
	 * All queries must be against the same index. Results are equivalent to
	 * array_map, maintaining order and key relationship between input $queries
	 * and $result.
	 *
	 * @param array $queries
	 * @param array $options
	 * @return array|null  null is query failure.  empty array is no result.  array is success
	 */
	public function findMulti( array $queries, array $options = array() ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$queries ) {
			return array();
		}

		foreach ( $queries as $key => $value ) {
			$queries[$key] = UUID::convertUUIDs( $value, 'alphadecimal' );
		}

		$keys = array_keys( reset( $queries ) );
		if ( isset( $options['sort'] ) && !is_array( $options['sort'] ) ) {
			$options['sort'] = ObjectManager::makeArray( $options['sort'] );
		}

		try {
			$index = $this->getIndexFor( $keys, $options );
			$res = $index->findMulti( $queries, $options );
		} catch ( NoIndexException $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': ' . $e->getMessage() );
			$res = $this->storage->findMulti( $queries, $this->convertToDbOptions( $options ) );
		}

		if ( $res === null ) {
			return null;
		}

		$output = array();
		foreach( $res as $index => $queryOutput ) {
			$output[$index] = array_map( array( $this, 'load' ), $queryOutput );
		}
		return $output;
	}

	/**
	 * Returns a boolean true/false if the find()-operation for the given
	 * attributes has already been resolves and doesn't need to query any
	 * outside cache/database.
	 * Determining if a find() has not yet been resolved may be useful so that
	 * additional data may be loaded at once.
	 *
	 * @param array $attributes Attributes to find()
	 * @param array[optional] $options Options to find()
	 * @return bool
	 */
	public function found( array $attributes, array $options = array() ) {
		return $this->foundMulti( array( $attributes ), $options );
	}

	/**
	 * Returns a boolean true/false if the findMulti()-operation for the given
	 * attributes has already been resolves and doesn't need to query any
	 * outside cache/database.
	 * Determining if a find() has not yet been resolved may be useful so that
	 * additional data may be loaded at once.
	 *
	 * @param array $queries Queries to findMulti()
	 * @param array[optional] $options Options to findMulti()
	 * @return bool
	 */
	public function foundMulti( array $queries, array $options = array() ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$queries ) {
			return true;
		}

		$keys = array_keys( reset( $queries ) );
		if ( isset( $options['sort'] ) && !is_array( $options['sort'] ) ) {
			$options['sort'] = ObjectManager::makeArray( $options['sort'] );
		}

		foreach( $queries as $key => $value ) {
			$queries[$key] = UUID::convertUUIDs( $value, 'alphadecimal' );
		}

		try {
			$index = $this->getIndexFor( $keys, $options );
			$res = $index->foundMulti( $queries, $options );
			return $res;
		} catch ( NoIndexException $e ) {
			wfDebugLog( 'Flow', __METHOD__ . ': ' . $e->getMessage() );
		}

		return false;
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
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$objectIds ) {
			return array();
		}
		$primaryKey = $this->storage->getPrimaryKeyColumns();
		$queries = array();
		$retval = null;
		foreach ( $objectIds as $id ) {
			//check internal cache
			$query = UUID::convertUUIDs(
				array_combine( $primaryKey, ObjectManager::makeArray( $id ) ),
				'alphadecimal'
			);
			$obj = $this->mapper->get( $query );
			if ( $obj === null ) {
				$queries[] = $query;
			} else {
				$retval[] = $obj;
			}
		}
		if ( $queries ) {
			$res = $this->findMulti( $queries );
			if ( $res ) {
				foreach ( $res as $row ) {
					// primary key is unique, but indexes still return their results as array
					// to be consistent. undo that for a flat result array
					$retval[] = reset( $row );
				}
			}
		}

		return $retval;
	}

	/**
	 * Returns a boolean true/false if the get()-operation for the given
	 * attributes has already been resolves and doesn't need to query any
	 * outside cache/database.
	 * Determining if a find() has not yet been resolved may be useful so that
	 * additional data may be loaded at once.
	 *
	 * @param string|integer $id Id to get()
	 * @return bool
	 */
	public function got( $id ) {
		return $this->gotMulti( array( $id ) );
	}

	/**
	 * Returns a boolean true/false if the getMulti()-operation for the given
	 * attributes has already been resolves and doesn't need to query any
	 * outside cache/database.
	 * Determining if a find() has not yet been resolved may be useful so that
	 * additional data may be loaded at once.
	 *
	 * @param array $objectIds Ids to getMulti()
	 * @return bool
	 */
	public function gotMulti( array $objectIds ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		if ( !$objectIds ) {
			return true;
		}

		$primaryKey = $this->storage->getPrimaryKeyColumns();
		$queries = array();
		foreach ( $objectIds as $id ) {
			$query = array_combine( $primaryKey, ObjectManager::makeArray( $id ) );
			$query = UUID::convertUUIDs( $query, 'alphadecimal' );
			if ( !$this->mapper->get( $query ) ) {
				$queries[] = $query;
			}
		}

		if ( $queries && $this->mapper instanceof CachingObjectMapper ) {
			return false;
		}

		return $this->foundMulti( $queries );
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

	/**
	 * @param array $keys
	 * @param array $options
	 * @return Index
	 * @throws NoIndexException
	 */
	public function getIndexFor( array $keys, array $options = array() ) {
		sort( $keys );
		$current = null;
		foreach ( $this->indexes as $index ) {
			// @var Index $index
			if ( !$index->canAnswer( $keys, $options ) ) {
				continue;
			}

			// make sure at least some index is picked
			if ( $current === null ) {
				$current = $index;

			// Find the smallest matching index
			} else if ( isset( $options['limit'] ) ) {
				$current = $index->getLimit() < $current->getLimit() ? $index : $current;

			// if no limit specified, find biggest matching index
			} else {
				$current = $index->getLimit() > $current->getLimit() ? $index : $current;
			}
		}
		if ( $current === null ) {
			$count = count( $this->indexes );
			throw new NoIndexException(
				"No index (out of $count) available to answer query for " . implode( ", ", $keys ) .
				' with options ' . FormatJson::encode( $options ), 'no-index'
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

	/**
	 * Convert index options to db equivalent options
	 */
	protected function convertToDbOptions( $options ) {
		$dbOptions = $orderBy = array();
		$order = '';

		if ( isset( $options['limit'] ) ) {
			$dbOptions['LIMIT'] = (int)$options['limit'];
		}

		if ( isset( $options['order'] ) ) {
			$order = ' ' . $options['order'];
		}
		if ( isset( $options['sort'] ) ) {
			foreach ( $options['sort'] as $val ) {
				$orderBy[] = $val . $order;
			}
		}
		if ( $orderBy ) {
			$dbOptions['ORDER BY'] = $orderBy;
		}

		return $dbOptions;
	}
}
