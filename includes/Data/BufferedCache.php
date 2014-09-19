<?php

namespace Flow\Data;

use BagOStuff;
use Flow\Exception\DataModelException;

/**
 * Wraps the write methods of memcache into a buffer which can be flushed
 */
class BufferedCache {
	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var array
	 */
	protected $buffer;

	/**
	 * @var integer
	 */
	protected $exptime;

	/**
	 * @var string Cache version
	 */
	protected $version;

	/**
	 * @param BagOStuff $cache The cache implementation to back this buffer with
	 * @param integer $exptime The default length of time to cache data. 0 for LRU.
	 * @param string $version String appended to all keys to achieve global cache versioning.
	 */
	public function __construct( BagOStuff $cache, $exptime, $version = '' ) {
		$this->cache = $cache;
		$this->exptime = $exptime;
		$this->version = $version === '' ? '' : ':' . $version;
	}

	/**
	 * @param string $key The cache key to fetch
	 * @return mixed
	 */
	public function get( $key ) {
		return $this->cache->get( $this->resolveKey( $key ) );
	}

	/**
	 * @param array $keys List of cache key strings to fetch
	 * @return array
	 */
	public function getMulti( array $keys ) {
		$res = $this->cache->getMulti( $this->resolveKeys( $keys ) );
		// While most of the BagOStuff implementations return an empty array on not
		// found from getMulti the memcached bag returns false
		if ( $res === false ) {
			return array();
		}
		// The memcached BagOStuff returns only existing keys,
		// but the redis BagOStuff puts a false for all keys
		// it doesn't find.  Resolve that inconsistency here
		// by filtering all false values.
		return $this->stripKeyVersions( array_filter( $res, function( $value ) {
			return $value !== false;
		} ) );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function add( $key, $value ) {
		$key = $this->resolveKey( $key );
		if ( $this->buffer === null ) {
			$this->cache->add( $key, $value, $this->exptime );
		} else {
			$this->buffer[] = array(
				'command' => array( $this->cache, __FUNCTION__ ),
				'arguments' => array( $key, $value, $this->exptime ),
			);
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		$key = $this->resolveKey( $key );
		if ( $this->buffer === null ) {
			$this->cache->set( $key, $value, $this->exptime );
		} else {
			$this->buffer[] = array(
				'command' => array( $this->cache, __FUNCTION__ ),
				'arguments' => array( $key, $value, $this->exptime ),
			);
		}
	}

	/**
	 * @param string $key
	 * @param integer $time
	 */
	public function delete( $key, $time = 0 ) {
		$key = $this->resolveKey( $key );
		if ( $this->buffer === null ) {
			$this->cache->delete( $key, $time );
		} else {
			$this->buffer[] = array(
				'command' => array( $this->cache, __FUNCTION__ ),
				'arguments' => compact( 'key', 'time' ),
			);
		}
	}

	/**
	 * @param string $key
	 * @param \Closure $callback
	 * @param int $attempts
	 * @return bool
	 */
	public function merge( $key, \Closure $callback, $attempts = 10 ) {
		/**
		 * Merge will CAS values, which could potentially fail (if, due to
		 * concurrent writes, cache has changed since)
		 * There will be multiple $attempts, but it may still fail.
		 * To reliable update the data in cache, we'll have to delete the cache
		 * if the CAS did not get through.
		 *
		 * Because we may be buffering the cache operation, I'll wrap both the
		 * CAS (merge) and the fallback delete into a closure. It can either be
		 * executed immediately (no buffer) or, if buffered, be committed via
		 * call_user_func_array.
		 *
		 * @param string $key
		 * @param \Closure $callback
		 * @param int $exptime
		 * @param int $attempts
		 */
		$cache = $this->cache;
		$merge = function ( $key, \Closure $callback, $exptime, $attempts ) use( $cache ) {
			$success = $cache->merge( $key, $callback, $exptime, $attempts );

			// if we failed to CAS new data, kill the cached value so it'll be
			// re-fetched from DB
			if ( !$success ) {
				$cache->delete( $key );
			}
			return $success;
		};

		$key = $this->resolveKey( $key );
		if ( $this->buffer === null ) {
			return $merge( $key, $callback, $this->exptime, $attempts );
		} else {
			$this->buffer[] = array(
				'command' => $merge,
				'arguments' => array( $key, $callback, $this->exptime, $attempts ),
			);
			return true;
		}
	}

	/**
	 * Begin buffering cache commands
	 *
	 * @throws \MWException When buffering is already enabled.
	 */
	public function begin() {
		if ( $this->buffer === null ) {
			$this->buffer = array();
		} else {
			throw new DataModelException( 'Transaction already in progress', 'process-data' );
		}
	}

	/**
	 * Write out all buffered commands to the cache
	 *
	 * @throws \MWException When no buffer has been enabled
	 */
	public function commit() {
		if ( $this->buffer === null ) {
			throw new \MWException( 'No transaction in progress' );
		}
		foreach ( $this->buffer as $row ) {
			call_user_func_array(
				$row['command'],
				$row['arguments']
			);
		}
		$this->buffer = null;
	}

	public function rollback() {
		if ( $this->buffer === null ) {
			throw new DataModelException( 'No transaction in progress', 'process-data' );
		}
		$this->buffer = null;
	}

	/**
	 * @param string $key Cache key to attach version to
	 * @return string Versioned cache key
	 */
	protected function resolveKey( $key ) {
		return $key . $this->version;
	}

	/**
	 * @param string[] $keys Cache keys to attach version to
	 * @return string[] Versioned cache keys
	 */
	protected function resolveKeys( array $keys ) {
		foreach ( $keys as $idx => $key ) {
			$keys[$idx] .= $this->version;
		}
		return $keys;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function stripKeyVersion( $key ) {
		return substr( $key, 0, -strlen( $this->version ) );
	}

	/**
	 * @param mixed[] Array indexed by versioned cache keys
	 * @return mixed[] Array indexed by cache keys with version stripped
	 */
	protected function stripKeyVersions( array $input ) {
		$len = -strlen( $this->version );
		$result = array();
		foreach ( $input as $key => $value ) {
			$result[substr( $key, 0, $len )] = $value;
		}
		return $result;
	}
}
