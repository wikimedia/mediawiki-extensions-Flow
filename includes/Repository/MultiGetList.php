<?php

namespace Flow\Repository;

use BagOStuff;

class MultiGetList {

	public function __construct( BagOStuff $cache ) {
		$this->cache = $cache;
	}

	public function get( $key, array $ids, $loadCallback ) {
		$key = implode( ':', (array) $key );
		$cacheKeys = array();
		foreach ( $ids as $id ) {
			if ( !is_scalar( $id ) ) {
				echo '<pre>';
				echo "Not Scalar: \n";
				var_dump( $ids );die();
			}
			$cacheKeys[wfForeignMemcKey( 'flow', '', $key, $id )] = $id;
		}
		return $this->getByKey( $cacheKeys, $loadCallback );
	}

	public function getByKey( array $cacheKeys, $loadCallback ) {
		if ( !$cacheKeys ) {
			return array();
		}
		$result = array();
		foreach ( $this->cache->getMulti( array_keys( $cacheKeys ) ) as $key => $value ) {
			$result[$cacheKeys[$key]] = $value;
			unset( $cacheKeys[$key] );
		}
		if ( count( $cacheKeys ) === 0 ) {
			return $result;
		}
		$res = call_user_func( $loadCallback, array_values( $cacheKeys ) );
		if ( !$res ) {
			// storage failure of some sort
			return $result;
		}
		$invCacheKeys = array_flip( $cacheKeys );
		foreach ( $res as $id => $row ) {
			$this->cache->set( $invCacheKeys[$id], $row );
			$result[$id] = $row;
		}

		return $result;
	}
}
