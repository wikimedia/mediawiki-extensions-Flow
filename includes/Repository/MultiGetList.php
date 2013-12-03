<?php

namespace Flow\Repository;

use BagOStuff;
use Flow\Model\UUID;

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
				throw new \InvalidArgumentException( 'Not scalar:' . gettype( $id ) );
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
				if ( $cacheKeys[$key] instanceof UUID ) {
					$idx = $cacheKeys[$key]->getBinary();
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
			// If we failed contacting memcache a moment ago dont bother trying to
			// push values either.
			if ( $multiRes !== false ) {
				$this->cache->set( $invCacheKeys[$id], $row, $this->cacheTime );
			}
			$result[$id] = $row;
		}

		return $result;
	}
}
