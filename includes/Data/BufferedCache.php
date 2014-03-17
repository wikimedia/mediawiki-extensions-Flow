<?php

namespace Flow\Data;

use BagOStuff;
use Flow\Exception\DataModelException;

/**
 * Wraps the write methods of memcache into a buffer which can be flushed
 */
class BufferedCache {
	protected $cache;
	protected $buffer;
	protected $exptime;

	/**
	 * @param BagOStuff $cache The cache implementation to back this buffer with
	 * @param integer $exptime The default length of time to cache data. 0 for LRU.
	 */
	public function __construct( BagOStuff $cache, $exptime ) {
		$this->cache = $cache;
		$this->exptime = $exptime;
	}

	/**
	 * @param string $key The cache key to fetch
	 * @return mixed
	 */
	public function get( $key ) {
		return $this->cache->get( $key );
	}

	/**
	 * @param array $keys List of cache key strings to fetch
	 * @return array
	 */
	public function getMulti( array $keys ) {
		return $this->cache->getMulti( $keys );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function add( $key, $value ) {
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
		};

		if ( $this->buffer === null ) {
			$merge( $key, $callback, $this->exptime, $attempts );
		} else {
			$this->buffer[] = array(
				'command' => $merge,
				'arguments' => array( $key, $callback, $this->exptime, $attempts ),
			);
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
}
