<?php

namespace Flow\Repository;

use BagOStuff;
use Flow\Model\UUID;
use Flow\Exception\InvalidInputException;

class MultiGetList {

	/**
	 * @param BagOStuff $cache
	 * @param integer $cacheTime
	 */
	public function __construct( BagOStuff $cache, $cacheTime ) {
		$this->cache = $cache;
		$this->cacheTime = $cacheTime;
	}

	public function get( $key, array $ids, $loadCallback ) {
		$key = implode( ':', (array) $key );
		$cacheKeys = array();
		foreach ( $ids as $id ) {
			if ( $id instanceof \Flow\Model\UUID ) {
				$cacheId = $id->getHex();
			} elseif ( !is_scalar( $id ) ) {
				throw new InvalidInputException( 'Not scalar:' . gettype( $id ), 'invalid-input' );
			} else {
				$cacheId = $id;
			}
			$cacheKeys[wfForeignMemcKey( 'flow', '', $key, $cacheId )] = $id;
		}
		return $this->getByKey( $cacheKeys, $loadCallback );
	}

	public function getByKey( array $cacheKeys, $loadCallback ) {
		if ( !$cacheKeys ) {
			return array();
		}
		$result = array();
		$multiRes = $this->cache->getMulti( array_keys( $cacheKeys ) );
		if ( $multiRes === false ) {
			// Falls through to query only backend
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Failure querying memcache' );
		} else {
			foreach ( $multiRes as $key => $value ) {
				if ( is_array( $value ) && isset( $value['k'] ) ) {
					$idx = $value['k'];
					$value = $value['v'];

				// BC; we now save both key & value in cache, but we didn't used
				// to do that, so "guess" the correct key for older caches
				} elseif ( $cacheKeys[$key] instanceof UUID ) {
					$idx = $cacheKeys[$key]->getHex();
				} else {
					$idx = $cacheKeys[$key];
				}
				$result[$idx] = $value;
				unset( $cacheKeys[$key] );
			}
		}
		if ( count( $cacheKeys ) === 0 ) {
			return $result;
		}
		$res = call_user_func( $loadCallback, array_values( $cacheKeys ) );
		if ( !$res ) {
			// storage failure of some sort
			return $result;
		}
		$invCacheKeys = array();
		foreach ( $cacheKeys as $cacheKey => $id ) {
			if ( $id instanceof UUID ) {
				$id = $id->getHex();
			}
			$invCacheKeys[$id] = $cacheKey;
		}
		foreach ( $res as $id => $row ) {
			// If we failed contacting memcache a moment ago don't bother trying to
			// push values either.
			if ( $multiRes !== false ) {
				$cache = array( 'k' => $id, 'v' => $row );
				$this->cache->set( $invCacheKeys[$id], $cache, $this->cacheTime );
			}
			$result[$id] = $row;
		}

		return $result;
	}
}
