<?php

namespace Flow\Data;

use Flow\DbFactory;
use WANObjectCache;

class FlowObjectCache {
	/**
	 * @var WANObjectCache
	 */
	protected $cache;

	/**
	 * @var int
	 */
	protected $ttl = 0;

	/**
	 * @var array
	 */
	protected $setOptions;

	/**
	 * @param WANObjectCache $cache The cache implementation to back this buffer with
	 * @param DbFactory $dbFactory
	 * @param int $ttl The default length of time to cache data. 0 for LRU.
	 */
	public function __construct( WANObjectCache $cache, DbFactory $dbFactory, $ttl = 0 ) {
		$this->ttl = $ttl;
		$this->cache = $cache;
		$this->setOptions = \Database::getCacheSetOptions( $dbFactory->getDB( DB_SLAVE ) );
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key ) {
		$this->log( __METHOD__, $key );
		return $this->cache->get( $key );
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	public function getMulti( array $keys ) {
		$this->log( __METHOD__, implode( ' ', $keys ) );
		return $this->cache->getMulti( $keys );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function set( $key, $value ) {
		$this->log( __METHOD__, $key );
		return $this->cache->set( $key, $value, $this->ttl, $this->setOptions );
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function setMulti( array $data ) {
		foreach ( $data as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return bool
	 */
	public function delete( $key, $time = 0 ) {
		$this->log( __METHOD__, $key );
		return $this->cache->delete( $key, $time );
	}

	private function log( $method, $key ) {
//		global $wgRequest;
//		/** @var \Psr\Log\LoggerInterface $logger */
//		$logger = Container::get( 'default_logger' );
//		$logger->debug(
//			"debug_xyz: {method} {httpMethod} {key}",
//			array(
//				'method' => $method,
//				'httpMethod' => $wgRequest->getMethod(),
//				'key' => $key,
//			)
//		);
	}
}
