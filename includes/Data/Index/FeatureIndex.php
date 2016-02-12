<?php

namespace Flow\Data\Index;

use Flow\Container;
use Flow\Data\BufferedCache;
use Flow\Data\Compactor;
use Flow\Data\Compactor\FeatureCompactor;
use Flow\Data\Compactor\ShallowCompactor;
use Flow\Data\Index;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectMapper;
use Flow\Data\ObjectStorage;
use Flow\Model\UUID;
use FormatJson;
use Flow\Exception\DataModelException;

/**
 * Index objects with equal features($indexedColumns) into the same buckets.
 */
abstract class FeatureIndex implements Index {

	/**
	 * @var BufferedCache
	 */
	protected $cache;

	/**
	 * @var ObjectStorage
	 */
	protected $storage;

	/**
	 * @var ObjectMapper
	 */
	protected $mapper;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var Compactor
	 */
	protected $rowCompactor;

	/**
	 * @var string[]
	 */
	protected $indexed;

	/**
	 * @var string[] The indexed columns in alphabetical order. This is
	 *  ordered so that cache keys can be generated in a stable manner.
	 */
	protected $indexedOrdered;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * {@inheritDoc}
	 */
	abstract public function getLimit();

	/**
	 * @return array The options used for querying self::$storage
	 */
	abstract public function queryOptions();

	/**
	 * @todo this doesn't need to be abstract
	 * @param array $values The current contents of a single feature bucket
	 * @return array $values trimmed to respect self::getLimit()
	 */
	abstract public function limitIndexSize( array $values );

	/**
	 * @todo Could the cache key be passed in instead of $indexed?
	 * @param array $indexed The portion of $row that makes up the cache key
	 * @param array $row A single row of data to add to its related feature bucket
	 */
	abstract protected function addToIndex( array $indexed, array $row );

	/**
	 * @todo Similar, Could the cache key be passed in instead of $indexed?
	 * @param array $indexed The portion of $row that makes up the cache key
	 * @param array $row A single row of data to remove from its related feature bucket
	 */
	abstract protected function removeFromIndex( array $indexed, array $row );

	/**
	 * @param BufferedCache $cache
	 * @param ObjectStorage $storage
	 * @param ObjectMapper $mapper
	 * @param string $prefix Prefix to utilize for all cache keys
	 * @param array $indexedColumns List of columns to index,
	 */
	public function __construct( BufferedCache $cache, ObjectStorage $storage, ObjectMapper $mapper, $prefix, array $indexedColumns ) {
		$this->cache = $cache;
		$this->storage = $storage;
		$this->mapper = $mapper;
		$this->prefix = $prefix;
		$this->rowCompactor = new FeatureCompactor( $indexedColumns );
		$this->indexed = $indexedColumns;
		// sort this and ksort in self::cacheKey to always have cache key
		// fields in same order
		sort( $indexedColumns );
		$this->indexedOrdered = $indexedColumns;
	}

	/**
	 * @return string[] The list of columns to bucket database rows by in
	 *  the same order as provided to the constructor.
	 */
	public function getPrimaryKeyColumns() {
		return $this->indexed;
	}

	/**
	 * {@inheritDoc}
	 */
	public function canAnswer( array $featureColumns, array $options ) {
		sort( $featureColumns );
		if ( $featureColumns !== $this->indexedOrdered ) {
			return false;
		}

		// This can probably be moved to TopKIndex if it's not used
		// by anything else.
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
	 * Rows are first sorted based on the first term of the result, then ties
	 * are broken by evaluating the second term and so on.
	 *
	 * @return string[]|false The columns to sort by, or false if no sorting is defined
	 */
	public function getSort() {
		return isset( $this->options['sort'] ) ? $this->options['sort'] : false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrder() {
		if ( isset( $this->options['order'] ) && strtoupper( $this->options['order'] ) === 'ASC' ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}

	/**
	 * Delete any feature bucket $object would be contained in from the cache
	 *
	 * @param object $object
	 * @param array $row
	 * @throws DataModelException
	 */
	public function cachePurge( $object, array $row ) {
		$indexed = ObjectManager::splitFromRow( $row, $this->indexed );
		if ( !$indexed ) {
			throw new DataModelException( 'Un-indexable row: ' . FormatJson::encode( $row ), 'process-data' );
		}
		// We don't want to just remove this object from the index, then the index would be incorrect.
		// We want to delete the bucket that contains this object.
		$this->cache->delete( $this->cacheKey( $indexed ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$indexed = ObjectManager::splitFromRow( $new , $this->indexed );
		// is un-indexable a bail-worthy occasion? Probably not but makes debugging easier
		if ( !$indexed ) {
			throw new DataModelException( 'Un-indexable row: ' . FormatJson::encode( $new ), 'process-data' );
		}
		$compacted = $this->rowCompactor->compactRow( UUID::convertUUIDs( $new, 'alphadecimal' ) );
		// give implementing index option to create rather than append
		if ( !$this->maybeCreateIndex( $indexed, $new, $compacted ) ) {
			// fall back to append
			$this->addToIndex( $indexed, $compacted );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$oldIndexed = ObjectManager::splitFromRow( $old, $this->indexed );
		$newIndexed = ObjectManager::splitFromRow( $new, $this->indexed );
		if ( !$oldIndexed ) {
			throw new DataModelException( 'Un-indexable row: ' . FormatJson::encode( $oldIndexed ), 'process-data' );
		}
		if ( !$newIndexed ) {
			throw new DataModelException( 'Un-indexable row: ' . FormatJson::encode( $newIndexed ), 'process-data' );
		}
		$oldCompacted = $this->rowCompactor->compactRow( UUID::convertUUIDs( $old, 'alphadecimal' ) );
		$newCompacted = $this->rowCompactor->compactRow( UUID::convertUUIDs( $new, 'alphadecimal' ) );
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

	/**
	 * {@inheritDoc}
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$indexed = ObjectManager::splitFromRow( $old, $this->indexed );
		if ( !$indexed ) {
			throw new DataModelException( 'Unindexable row: ' . FormatJson::encode( $old ), 'process-data' );
		}
		$compacted = $this->rowCompactor->compactRow( UUID::convertUUIDs( $old, 'alphadecimal' ) );
		$this->removeFromIndex( $indexed, $compacted );
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterLoad( $object, array $old ) {
		// nothing to do
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterClear() {
		// nothing to do
	}

	/**
	 * {@inheritDoc}
	 */
	public function find( array $attributes, array $options = array() ) {
		$results = $this->findMulti( array( $attributes ), $options );
		return reset( $results );
	}

	/**
	 * {@inheritDoc}
	 */
	public function findMulti( array $queries, array $options = array() ) {
		if ( !$queries ) {
			return array();
		}

		// get cache keys for all queries
		$cacheKeys = $this->getCacheKeys( $queries );

		// retrieve from cache (only query duplicate queries once)
		// $fromCache will be an array containing compacted results as value and
		// cache keys as key
		$fromCache = $this->cache->getMulti( array_unique( $cacheKeys ) );

		// figure out what queries were resolved in cache
		// $keysFromCache will be an array where values are cache keys and keys
		// are the same index as their corresponding $queries
		// (intersect with $cacheKeys to guarantee order)
		$keysFromCache = array_intersect( $cacheKeys, array_keys( $fromCache ) );

		// filter out all queries that have been resolved from cache and fetch
		// them from storage
		// $fromStorage will be an array containing (expanded) results as value
		// and indexes matching $query as key
		$storageQueries = array_diff_key( $queries, $keysFromCache );
		$fromStorage = array();
		if ( $storageQueries ) {
			$fromStorage = $this->backingStoreFindMulti( $storageQueries );
		}

		$results = $fromStorage;

		// $queries may have had duplicates that we've ignored to minimize
		// cache requests - now re-duplicate values from cache & match the
		// results against their respective original keys in $queries
		foreach ( $keysFromCache as $index => $cacheKey ) {
			$results[$index] = $fromCache[$cacheKey];
		}

		// now that we have all data, both from cache & backing storage, filter
		// out all data we don't need
		$results = $this->filterResults( $results, $options );

		// if we have no data from cache, there's nothing left - quit early
		if ( !$fromCache ) {
			return $results;
		}

		// because we may have combined data from 2 different sources, chances
		// are the order of the data is no longer in sync with the order
		// $queries were in - fix that by replacing $queries values with
		// the corresponding $results value
		// note that there may be missing results, hence the intersect ;)
		$order = array_intersect_key( $queries, $results );
		$results = array_replace( $order, $results );

		$keyToQuery = array();
		foreach ( $keysFromCache as $index => $key ) {
			// all redundant data has been stripped, now expand all cache values
			// (we're only doing this now to avoid expanding redundant data)
			$fromCache[$key] = $results[$index];

			// to expand rows, we'll need the $query info mapped to the cache
			// key instead of the $query index
			if ( !isset( $keyToQuery[$key] ) ) {
				$keyToQuery[$key] = $queries[$index];
				$keyToQuery[$key] = UUID::convertUUIDs( $keyToQuery[$key], 'alphadecimal' );
			}
		}

		// expand and replace the stubs in $results with complete data
		$fromCache = $this->rowCompactor->expandCacheResult( $fromCache, $keyToQuery );
		foreach ( $keysFromCache as $index => $cacheKey ) {
			$results[$index] = $fromCache[$cacheKey];
		}

		return $results;
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
		// Overriden in TopKIndex
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
				$keyToQuery[$key] = $queries[$i];
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

	/**
	 * Query persistent storage for data not found in cache.  Note that this
	 * does not use the query options because an individual bucket contents is
	 * based on constructor options, and not query options.  Query options merely
	 * change what part of the bucket is returned(or if the query has to fail over
	 * to direct from storage due to being beyond the set of cached values).
	 *
	 * @param array $queries
	 * @return array
	 */
	protected function backingStoreFindMulti( array $queries ) {
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
			$results[$idx] = $rows;
			unset( $queries[$idx] );
		}

		if ( count( $queries ) !== 0 ) {
			// Log something about not finding everything?
		}

		return $results;
	}

	/**
	 * Called prior to self::addToIndex only when new objects as inserted.  Gives the
	 * opportunity for indexes to create rather than append if this object signifies a new
	 * feature list.
	 *
	 * @todo again, could just pass cache key instead of $indexed?
	 * @param array $indexed The values that make up the cache key
	 * @param array $sourceRow The input database row
	 * @param array $compacted The database row reduced in size for storage within the index
	 * @return boolean True if an index was created, or false if $sourceRow should be merged
	 *  into the index via self::addToIndex
	 */
	protected function maybeCreateIndex( array $indexed, array $sourceRow, array $compacted ) {
		return false;
	}

	/**
	 * Called to update a row's data within a feature bucket.
	 *
	 * Note that this naive implementation does two round trips, likely an implementing
	 * class can do this in a single round trip.
	 *
	 * @todo again, could just pass cache key instead of $indexed?
	 * @param array $indexed The values that make up the cache key
	 * @param array $old The database row that was previously retrieved from cache
	 * @param array $new The new version of that replacement row
	 */
	protected function replaceInIndex( array $indexed, array $old, array $new ) {
		$this->removeFromIndex( $indexed, $old );
		$this->addToIndex( $indexed, $new );
	}

	/**
	 * Generate the cache key representing the attributes
	 * @param array $attributes
	 * @return string
	 */
	protected function cacheKey( array $attributes ) {
		foreach( $attributes as $key => $attr ) {
			if ( $attr instanceof UUID ) {
				$attributes[$key] = $attr->getAlphadecimal();
			} elseif ( strlen( $attr ) === UUID::BIN_LEN && substr( $key, -3 ) === '_id' ) {
				$attributes[$key] = UUID::create( $attr )->getAlphadecimal();
			}
		}

		// values in $attributes may not always be in the exact same order,
		// which would lead to differences in cache key if we don't force that
		ksort( $attributes );

		return wfForeignMemcKey( self::cachedDbId(), '', $this->prefix, md5( implode( ':', $attributes ) ), Container::get( 'cache.version' ) );
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
