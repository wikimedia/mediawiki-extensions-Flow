<?php

namespace Flow\Data;

// Untested method of handling duplicate requests for the same data
// Preserves any BagOStuff semantics like BufferedCache does
class LocalBufferedCache extends BufferedCache {

	/**
	 * Local cache
	 *
	 * @var array
	 */
	protected $internal = array();

	/**
	 * List of update to localCache upon cache commit
	 *
	 * @var \Closure[]
	 */
	protected $bufferUpdate = array();

	/**
	 * Returns true if the data is in own "storage" already, or false if it will
	 * need to be fetched from external cache.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->internal );
	}

	public function get( $key ) {
		if ( $this->has( $key ) ) {
			return $this->internal[$key];
		}
		return $this->internal[$key] = parent::get( $key );
	}

	public function getMulti( array $keys ) {
		$found = array();
		foreach ( $keys as $idx => $key ) {
			if ( $this->has( $key ) ) {
				// BagOStuff::multiGet doesn't return the unfound keys
				if ( $this->internal[$key] !== false ) {
					$found[$key] = $this->internal[$key];
				}
				unset( $keys[$idx] );
			}
		}
		if ( $keys ) {
			$flipped = array_flip( $keys );
			$res = parent::getMulti( $keys );
			if ( $res === false ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Failure requesting data from memcache : ' . implode( ',', $keys ) );
				return $found;
			}
			foreach ( $res as $key => $value ) {
				$this->internal[$key] = $found[$key] = $value;
				unset( $keys[$flipped[$key]] );
			}
			// BagOStuff::multiGet doesn't return the unfound keys, but we cache the result
			foreach ( $keys as $key ) {
				$this->internal[$key] = false;
			}
		}
		return $found;
	}

	// @Todo - Use bufferUpdate[] to update local cache if buffer cache is enabled,
	// need to make sure that no code relies on the new dangling local cache value
	public function add( $key, $value ) {
		if ( $this->buffer === null ) {
			if ( $this->cache->add( $key, $value, $this->exptime ) ) {
				$this->internal[$key] = $value;
			}
		} else {
			$this->buffer[] = array(
				'command' => array( $this->cache, __FUNCTION__ ),
				'arguments' => array( $key, $value, $this->exptime ),
			);
			// speculative ... could cause a ton of bugs due to normal assumptions
			// how to do this reasonably?
			if ( !$this->has( $key ) || $this->internal[$key] === false ) {
				$this->internal[$key] = $value;
			}
		}
	}

	// @Todo - Use bufferUpdate[] to update local cache if buffer cache is enabled,
	// need to make sure that no code relies on the new dangling local cache value
	public function set( $key, $value ) {
		parent::set( $key, $value );
		$this->internal[$key] = $value;
	}

	public function merge( $key, \Closure $callback, $attempts = 10 ) {
		parent::merge( $key, $callback, $attempts );

		// data is being merged into this key, so invalidate the cached version
		if ( $this->buffer === null ) {
			unset( $this->internal[$key] );
		} else {
			$internal =& $this->internal;
			$this->bufferUpdate[] = function() use ( $key, &$internal ) {
				unset( $internal[$key] );
			};
		}
	}

	public function commit() {
		parent::commit();

		foreach ( $this->bufferUpdate as $closure ) {
			call_user_func( $closure );
		}
		$this->bufferUpdate = array();
	}
}
