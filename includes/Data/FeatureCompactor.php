<?php

namespace Flow\Data;

use Flow\Exception\DataModelException;

/**
 * Removes the feature fields from stored array since its duplicating the cache key values
 * Re-adds them when retrieving from cache.
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
	 * @throws DataModelException
	 */
	public function expandCacheResult( array $cached, array $keyToQuery ) {
		foreach ( $cached as $key => $rows ) {
			$query = $keyToQuery[$key];
			foreach ( $rows as $k => $row ) {
				$cached[$key][$k] += $query;
			}
		}

		return $cached;
	}
}
