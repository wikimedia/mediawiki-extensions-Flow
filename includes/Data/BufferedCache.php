<?php

namespace Flow\Data;

use Flow\Container;
use Flow\DbFactory;
use WANObjectCache;

/**
 * todo: rename this as it's not buffered anymore
 */
class BufferedCache {
	/**
	 * @var WANObjectCache
	 */
	protected $cache;

	/**
	 * @var int
	 */
	protected $exptime = 0;

	/**
	 * @param WANObjectCache $cache The cache implementation to back this buffer with
	 * @param int $exptime The default length of time to cache data. 0 for LRU.
	 */
	public function __construct( WANObjectCache $cache, $exptime = 0 ) {
		$this->exptime = $exptime;
		$this->cache = $cache;
	}

	/**
	 * @param string $key
	 * @param Callable|null $setCallback
	 * @return mixed
	 */
	public function get( $key, $setCallback = null ) {
		$this->log( __METHOD__, $key );
		if ( $setCallback ) {
			/** @var DbFactory $dbFactory */
			$dbFactory = Container::get( 'db.factory' );
			return $this->cache->getWithSetCallback(
				$key,
				$this->exptime,
				$setCallback,
				/* do we know if this is the same slave used by the storage? */
				\Database::getCacheSetOptions( $dbFactory->getDB( DB_SLAVE ) )
			);
		} else {
			return $this->cache->get( $key );
		}
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
		return $this->cache->set( $key, $value, $this->exptime );
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
