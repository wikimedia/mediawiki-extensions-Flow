<?php

namespace Flow\Data\BagOStuff;

use BagOStuff;
use HashBagOStuff;

/**
 * This class will serve as a local buffer to the real cache.
 *
 * It will pass reads on to the real cache, but defer writes. This makes it
 * possible to not do any cache updates until we can guarantee it's safe (e.g.
 * until we successfully commit everything to real storage)
 *
 * There will be some trickery to make sure that, after we've made changes to
 * cache (but that are still deferred), we don't read from the real cache
 * anymore, but instead serve the in-memory equivalent that we'll be writing to
 * real cache when all goes well.
 */
class BufferedBagOStuff extends HashBagOStuff {
	/**
	 * The real cache we'll eventually want to store to.
	 *
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * Deferred updates to be committed to real cache.
	 *
	 * @var array
	 */
	protected $buffer = array();

	/**
	 * Whether or not to defer updates.
	 *
	 * @var bool
	 */
	protected $transaction = false;

	/**
	 * Array of keys we've written to. They'll briefly be stored here after
	 * being committed, until all other writes in the transaction have been
	 * committed. This way, if a later write fails, we can invalidate previous
	 * updates based on those keys we wrote to.
	 *
	 * @var array
	 */
	protected $committed = array();

	/**
	 * @param BagOStuff $cache
	 */
	public function __construct( BagOStuff $cache ) {
		$this->cache = $cache;
		parent::__construct();
	}

	/**
	 * We only want expire to check if the key is expired. Parent expire will
	 * delete any such keys, but we want to keep them around, because otherwise
	 * we won't be able to discern between "deleted from buffered cache" and
	 * "not available in local cache, so let's get it from real cache."
	 *
	 * @param string $key
	 * @return bool
	 */
	protected function expire( $key ) {
		$expiry = $this->bag[$key][1];

		if ( $expiry == 0 || $expiry > time() ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $key
	 * @param int $flags [optional]
	 * @return bool|mixed
	 */
	protected function doGet( $key, $flags = 0 ) {
		if ( !isset( $this->bag[$key] ) ) {
			// Unknown in local cache = fetch from source cache
			$value = $this->cache->get( $key, $flags );
		} else {
			$value = parent::doGet( $key, $flags );
		}

		return $value;
	}

	/**
	 * @param array $keys
	 * @param int $flags [optional]
	 * @return array
	 */
	public function getMulti( array $keys, $flags = 0 ) {
		$values = array();

		// Retrieve all that we can from local cache
		foreach ( $keys as $key ) {
			$result = parent::get( $key );

			// If we found a real result, no need to go request if from real cache
			if ( $result !== false ) {
				$values[$key] = $result;
				unset( $keys[$key] );
			}
		}

		// Fetch the rest from real cache
		if ( $keys ) {
			$result = $this->cache->getMulti( $keys );

			// While most of the BagOStuff implementations return an empty array
			// on not found from getMulti the memcached bag returns false
			$result = $result ?: array();

			$values += $result;
		}

		// The memcached BagOStuff returns only existing keys, but the redis
		// BagOStuff puts a false for all keys it doesn't find.  Resolve that
		// inconsistency here by filtering all false values
		return array_filter( $values, function( $value ) {
			return $value !== false;
		} );
	}

	public function set( $key, $value, $exptime = 0, $flags = 0 ) {
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args(), $key );

		// Store the value in memory, so that when we ask for it again later in
		// this same request, we get the value we just set
		return parent::set( $key, $value, $exptime, $flags );
	}

	/**
	 * @param array $data
	 * @param int $exptime
	 * @return bool
	 */
	public function setMulti( array $data, $exptime = 0 ) {
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args(), array_keys( $data ) );

		$success = true;

		foreach ( $data as $key => $value ) {
			// Store the values in memory, so that when we ask for it again later in
			// this same request, we get the value we just set
			if ( !parent::set( $key, $value, $exptime ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 */
	public function add( $key, $value, $exptime = 0 ) {
		if ( $this->get( $key ) === false ) {
			$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args(), $key );

			// Store the values in memory, so that when we ask for it again later in
			// this same request, we get the value we just set
			return parent::set( $key, $value, $exptime );
		}

		return false;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete( $key ) {
		$cache = $this->cache;

		/**
		 * We'll use the return value of all buffered writes to check if they
		 * should be "rolled back" (which means deleting the keys to prevent
		 * corruption).
		 *
		 * delete() can return false if the delete was issued on a non-existing
		 * key. That is no corruption of data, though (the requested action
		 * actually succeeded: the key is done). Instead, make this callback
		 * always return true, regardless of whether or not the key existed.
		 *
		 * @param string $key
		 * @return bool
		 */
		$delete = function ( $key ) use ( $cache ) {
			$cache->delete( $key );
			return true;
		};

		$this->defer( $delete, func_get_args(), $key );

		// Check the current value to see if is currently exists, so we can
		// properly return true/false as would be expected from other BagOStuff
		$value = $this->get( $key );

		// To make sure that subsequent get() calls for this key don't return
		// a value (it's supposed to be deleted), we'll make it is expired in
		// our temporary bag.
		parent::set( $key, '', -1 );

		return $value !== false;
	}

	public function lock( $key, $timeout = 6, $expiry = 6, $rclass = '' ) {
		// Do not try to defer/buffer stuff
		return $this->cache->lock( $key, $timeout, $expiry, $rclass );
	}

	public function unlock( $key ) {
		// Do not try to defer/buffer stuff
		return $this->cache->unlock( $key );
	}

	/**
	 * Initiate a transaction: this will defer all writes to real cache until
	 * commit() is called.
	 */
	public function begin() {
		$this->transaction = true;
	}

	/**
	 * Commits all deferred updates to real cache.
	 *
	 * @return bool
	 */
	public function commit() {
		foreach ( $this->buffer as $update ) {
			$success = call_user_func_array( $update[0], $update[1] );

			// Store keys that data has been written to (so we can rollback)
			$this->committed += array_flip( $update[2] );

			// If we failed to commit data at any point, roll back
			if ( !$success ) {
				$this->rollback();
				return false;
			}
		}

		$this->clearLocal();
		$this->transaction = false;

		return true;
	}

	/**
	 * Roll back all scheduled changes.
	 */
	public function rollback() {
		// Delete all those keys from cache, they may be corrupt
		foreach ( $this->committed as $key => $nop ) {
			$this->cache->delete( $key );
		}

		// Always clear local cache values when something went wrong
		$this->bag = array();
		$this->clearLocal();

		$this->transaction = false;
	}

	/**
	 * @param callable $callback
	 * @param array $arguments
	 * @param string|string[] $key Key(s) being written to
	 */
	protected function defer( $callback, $arguments, $key ) {
		// Keys can be either 1 single string or array of multiple keys
		$keys = (array) $key;

		$this->buffer[] = array( $callback, $arguments, $keys );

		// persist to real cache immediately, if we're not in a "transaction"
		if ( !$this->transaction ) {
			$this->commit();
		}
	}

	protected function clearLocal() {
		$this->bag = array();
		$this->buffer = array();
		$this->committed = array();
	}
}
