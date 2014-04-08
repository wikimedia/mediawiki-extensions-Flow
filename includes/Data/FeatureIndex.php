<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\UUID;
use FormatJson;
use Flow\Exception\DataModelException;

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
	protected $options;

	// This exists in the Index interface and as such can't be abstract
	// until php 5.3.9, but some of our test machines are on 5.3.3
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

	/**
	 * @return array|false
	 */
	public function getSort() {
		return isset( $this->options['sort'] ) ? $this->options['sort'] : false;
	}

	/**
	 * @param array $rows
	 * @param array $options
	 * @return array [offset, limit]
	 */
	protected function getOffsetLimit( $rows, $options ) {
		$limit = isset( $options['limit'] ) ? $options['limit'] : $this->getLimit();

		if ( !isset( $options['offset-key'] ) ) {
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

		$offset = $this->getOffsetFromKey( $rows, $offsetKey );

		if ( $dir === 'fwd' ) {
			$startPos = $offset + 1;
		} elseif ( $dir === 'rev' ) {
			$startPos = $offset - $limit;

			if ( $startPos < 0 ) {
				if (
					isset( $options['offset-elastic'] ) &&
					$options['offset-elastic'] === false
				) {
					// If non-elastic, then reduce the number of items shown commensurately
					$limit += $startPos;
				}
				$startPos = 0;
			}
		} else {
			$startPos = 0;
		}

		return array( $startPos, $limit );
	}

	/**
	 * @param array $rows
	 * @param $offsetKey
	 * @return int
	 * @throws DataModelException
	 */
	protected function getOffsetFromKey( $rows, $offsetKey ) {
		$rowIndex = 0;
		foreach ( $rows as $row ) {
			$rowIndex++;
			$comparisonValue = $this->compareRowToOffset( $row, $offsetKey );
			if ( $comparisonValue <= 0 ) {
				return $rowIndex;
			}
		}

		throw new DataModelException( 'Unable to find specified offset in query results', 'process-data' );
	}

	/**
	 * @param $row
	 * @param $offset
	 * @return int
	 * @throws DataModelException
	 */
	public function compareRowToOffset( $row, $offset ) {
		$sortFields = $this->getSort();
		$splitOffset = explode( '|', $offset );
		$fieldIndex = 0;

		if ( $sortFields === false ) {
			throw new DataModelException( 'This Index implementation does not support key offsets', 'process-data' );
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
			throw new DataModelException( 'Unindexable row: ' .FormatJson::encode( $new ), 'process-data' );
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
			throw new DataModelException( 'Unindexable row: ' .FormatJson::encode( $oldIndexed ), 'process-data' );
		}
		if ( !$newIndexed ) {
			throw new DataModelException( 'Unindexable row: ' .FormatJson::encode( $newIndexed ), 'process-data' );
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
			throw new DataModelException( 'Unindexable row: ' .FormatJson::encode( $old ), 'process-data' );
		}
		$this->removeFromIndex( $indexed, $old );
	}

	public function onAfterLoad( $object, array $old ) {
		// nothing to do
	}

	public function find( array $attributes, array $options = array() ) {
		$results = $this->findMulti( array( $attributes ), $options );
		return reset( $results );
	}

	public function findMulti( array $queries, array $options = array() ) {
		if ( !$queries ) {
			return array();
		}

		// get cache keys for all queries
		$cacheKeys = $this->getCacheKeys( $queries );

		$results = $keyToQuery = array();
		foreach ( $cacheKeys as $i => $key ) {
			// These results will be merged into the query results, and as such need binary
			// uuid's as would be received from storage
			if ( !isset( $keyToQuery[$key] ) ) {
				$keyToQuery[$key] = UUID::convertUUIDs( $queries[$i] );
			}
		}

		// retrieve from cache (only query duplicate queries once)
		$cached = $this->cache->getMulti( array_unique( $cacheKeys ) );
		$cached = $this->filterResults( $cached, $options );
		$cached = $this->rowCompactor->expandCacheResult( $cached, $keyToQuery );

		foreach ( $cached as $key => $result ) {
			$indexes = array_keys( $cacheKeys, $key );
			foreach ( $indexes as $i ) {
				// write to complete results array (where the index of the
				// result matches the index of the given queries)
				$results[$i] = $result;

				// filter out queries that have been resolved from cache
				unset( $queries[$i] );
			}
		}

		// whatever hasn't been found in cache - fetch it from storage
		if ( $queries ) {
			$remaining = $this->backingStoreFindMulti( $queries, $cacheKeys );
			$remaining = $this->filterResults( $remaining, $options );

			// keys are exactly the same as they were in $queries, so we can +
			// the result arrays without worrying about overwriting stuff that
			// has the same key
			$results += $remaining;
		}

		// now make sure $results are in the same order $queries was (use
		// $cacheKeys for references, because $queries has been cleaned out)
		$ordered = array();
		foreach ( $cacheKeys as $i => $key ) {
			if ( isset( $results[$i] ) ) {
				$ordered[$i] = $results[$i];
			}
		}

		return $ordered;
	}

	/**
	 * Get rid of unneeded, according to the given $options.
	 *
	 * This is used to strip entries before expanding them;
	 * basically, at that point, we may only have a list of ids, which we need
	 * to expand (= fetch from cache) - don't want to do this for more than
	 * what is needed
	 *
	 * @param array $results
	 * @param array[optional] $options
	 * @return array
	 */
	protected function filterResults( array $results, array $options = array() ) {
		foreach ( $results as $i => $result ) {
			list( $offset, $limit ) = $this->getOffsetLimit( $result, $options );
			$results[$i] = array_slice( $result, $offset, $limit, true );
		}

		return $results;
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
		if ( !$queries ) {
			return true;
		}

		// get cache keys for all queries
		$cacheKeys = $this->getCacheKeys( $queries );

		// check if cache has a way of identifying what's stored locally
		if ( !method_exists( $this->cache, 'has' ) ) {
			return false;
		}

		// check if keys matching given queries are already known in local cache
		foreach ( $cacheKeys as $key ) {
			if ( !$this->cache->has( $key ) ) {
				return false;
			}
		}

		$keyToQuery = array();
		foreach ( $cacheKeys as $i => $key ) {
			// These results will be merged into the query results, and as such need binary
			// uuid's as would be received from storage
			if ( !isset( $keyToQuery[$key] ) ) {
				$keyToQuery[$key] = UUID::convertUUIDs( $queries[$i] );
			}
		}

		// retrieve from cache - this is cheap, it's is local storage
		$cached = $this->cache->getMulti( $cacheKeys );
		foreach ( $cached as $i => $result ) {
			$limit = isset( $options['limit'] ) ? $options['limit'] : $this->getLimit();
			$cached[$i] = array_splice( $result, 0, $limit );
		}

		// if we have a shallow compactor, the returned data are PKs of objects
		// that need to be fetched too
		if ( $this->rowCompactor instanceof ShallowCompactor ) {
			// test of the keys to be expanded are already in local cache
			$duplicator = $this->rowCompactor->getResultDuplicator( $cached, $keyToQuery );
			$queries = $duplicator->getUniqueQueries();
			if ( !$this->rowCompactor->getShallow()->foundMulti( $queries ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Build a map from cache key to its index in $queries.
	 *
	 * @param array $queries
	 * @return array Array of [query index => cache key]
	 * @throws DataModelException
	 */
	protected function getCacheKeys( $queries ) {
		$idxToKey = array();
		foreach ( $queries as $idx => $query ) {
			ksort( $query );
			if ( array_keys( $query ) !== $this->indexedOrdered ) {
				throw new DataModelException(
					'Cannot answer query for columns: ' . implode( ', ', array_keys( $queries[$idx] ) ), 'process-data'
				);
			}
			$key = $this->cacheKey( $query );
			$idxToKey[$idx] = $key;
		}

		return $idxToKey;
	}

	protected function backingStoreFindMulti( array $queries, array $cacheKeys ) {
		// query backing store
		$options = $this->queryOptions();
		$stored = $this->storage->findMulti( $queries, $options );
		$results = array();

		// map store results to cache key
		foreach ( $stored as $idx => $rows ) {
			if ( !$rows ) {
				// Nothing found,  should we cache failures as well as success?
				continue;
			}
			foreach ( $rows as $row ) {
				foreach ( $row as $k => $foo ) {
					if ( $foo !== null && !is_scalar( $foo ) ) {
						throw new DataModelException( "Received non-scalar row value for '$k' from: " . get_class( $this->storage ), 'process-data' );
					}
				}
			}
			$this->cache->add( $cacheKeys[$idx], $this->rowCompactor->compactRows( $rows ) );
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
			if ( $attr instanceof UUID ) {
				$attributes[$key] = $attr->getAlphadecimal();
			} elseif ( strlen( $attr ) === UUID::BIN_LEN && substr( $key, -3 ) === '_id' ) {
				$attributes[$key] = UUID::create( $attr )->getAlphadecimal();
			}
		}

		return wfForeignMemcKey( self::cachedDbId(), '', $this->prefix, implode( ':', $attributes ), Container::get( 'cache.version' ) );
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
