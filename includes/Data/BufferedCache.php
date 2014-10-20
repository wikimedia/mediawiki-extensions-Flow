<?php

namespace Flow\Data;

use BagOStuff;

/**
 * This class will emulate a BagOStuff, but with a fixed expiry time for all
 * writes. All methods will be passed on to the BagOStuff in constructor.
 * Preserves any BagOStuff semantics for the most common methods.
 */
class BufferedCache {
	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var int
	 */
	protected $exptime = 0;

	/**
	 * @param BagOStuff $cache The cache implementation to back this buffer with
	 * @param int $exptime The default length of time to cache data. 0 for LRU.
	 */
	public function __construct( BagOStuff $cache, $exptime = 0 ) {
		$this->exptime = $exptime;
		$this->cache = $cache;
	}

	/**
	 * @param string $key
	 * @param null $casToken
	 * @return mixed
	 */
	public function get( $key, &$casToken = null ) {
		return $this->cache->get( $key, $casToken );
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	public function getMulti( array $keys ) {
		return $this->cache->getMulti( $keys );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function set( $key, $value ) {
		return $this->cache->set( $key, $value, $this->exptime );
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function setMulti( array $data ) {
		return $this->cache->setMulti( $data, $this->exptime );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function add( $key, $value ) {
		return $this->cache->add( $key, $value, $this->exptime );
	}

	/**
	 * @param mixed $casToken
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function cas( $casToken, $key, $value ) {
		return $this->cache->cas( $casToken, $key, $value, $this->exptime );
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return bool
	 */
	public function delete( $key, $time = 0 ) {
		return $this->cache->delete( $key, $time );
	}

	/**
	 * @param string $key
	 * @param \Closure $callback
	 * @param int $attempts
	 * @return bool
	 */
	public function merge( $key, \Closure $callback, $attempts = 10 ) {
		return $this->cache->merge( $key, $callback, $this->exptime, $attempts );
	}

	/**
	 * Catches all other method calls & passes them on to the real cache.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call( $name, array $arguments ) {
		return call_user_func_array( array( $this->cache, $name ), $arguments );
	}
}
