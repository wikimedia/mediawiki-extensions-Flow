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
	 * We'll return stub CAS tokens in order to reliably replay the CAS actions
	 * later on. This will hold a map of stub token => value at that time.
	 *
	 * @see cas()
	 * @var array
	 */
	protected $casTokens = array();

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
	 * @param mixed $casToken [optional]
	 * @return bool|mixed
	 */
	public function get( $key, &$casToken = null ) {
		if ( !isset( $this->bag[$key] ) ) {
			// Unknown in local cache = fetch from source cache
			$value = $this->cache->get( $key, $casToken );
		} else {
			$value = parent::get( $key, $casToken );
		}

		// $casToken will be unreliable to the deferred updates so generate
		// a custom one and keep the associated value around.
		// Read more details in PHPDoc for function cas().
		// uniqid is ok here. Doesn't really have to be unique across
		// servers, just has to be unique every time it's called in this
		// one particular request - which it is.
		$casToken = uniqid();
		$this->casTokens[$casToken] = serialize( $value );

		return $value;
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	public function getMulti( array $keys ) {
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

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 */
	public function set( $key, $value, $exptime = 0 ) {
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args(), $key );

		// Store the value in memory, so that when we ask for it again later in
		// this same request, we get the value we just set
		return parent::set( $key, $value, $exptime );
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
	 * Since our CAS is deferred, the CAS token we got from our original
	 * get() will likely not be valid by the time we want to store it to
	 * the real cache. Imagine this scenario:
	 * * a value is fetched from (real) cache
	 * * an new value key is CAS'ed (into temp cache - real CAS is deferred)
	 * * this key's value is fetched again (this time from temp cache)
	 * * and a new value is CAS'ed again (into temp cache...)
	 *
	 * In this scenario, when we finally want to replay the write actions
	 * onto the real cache, the first 3 actions would likely work fine.
	 * The last (second CAS) however would not, since it never got a real
	 * updated $casToken from the real cache.
	 *
	 * To work around this problem, all get() calls will return a unique
	 * CAS token and store the value-at-that-time associated with that
	 * token. All we have to do when we want to write the data to real cache
	 * is, right before was CAS for real, get the value & (real) cas token
	 * from storage & compare that value to the one we had stored. If that
	 * checks out, we can safely resume the CAS with the real token we just
	 * received.
	 *
	 * Should a deferred CAS fail, however, we'll delete the key in cache
	 * since it's no longer reliable.
	 *
	 * @param mixed $casToken
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 */
	protected function cas( $casToken, $key, $value, $exptime = 0 ) {
		$cache = $this->cache;
		$originalValue = isset( $this->casTokens[$casToken] ) ? $this->casTokens[$casToken] : null;

		/**
		 * @param mixed $casToken
		 * @param string $key
		 * @param mixed $value
		 * @param int $exptime
		 * @return bool
		 */
		$cas = function ( $casToken, $key, $value, $exptime = 0 ) use ( $cache, $originalValue ) {
			// Check if given (local) CAS token was known
			if ( $originalValue === null ) {
				return false;
			}

			// Fetch data from real cache, getting new valid CAS token
			$current = $cache->get( $key, $casToken );

			// Check if the value we just read from real cache is still the same
			// as the one we saved when doing the original fetch
			if ( serialize( $current ) === $originalValue ) {
				/*
				 * Note that all BagOStuff::cas implementations are protected!
				 * We can still call it from here because this class too extends
				 * from BagOStuff, where the cas method is defined. PHP will
				 * allow us access because "because the implementation specific
				 * details are already known."
				 */

				// Everything still checked out, let's CAS the value for real now
				return $cache->cas( $casToken, $key, $value, $exptime );
			}

			return false;
		};

		// CAS value to local cache/memory
		$success = false;
		if ( serialize( $this->get( $key ) ) === $originalValue ) {
			$success = parent::set( $key, $value, $exptime );
		}

		// Only schedule the CAS to be performed on real cache if it was OK on
		// local cache
		if ( $success ) {
			$this->defer( $cas, func_get_args(), $key );
		}

		return $success;
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return bool
	 */
	public function delete( $key, $time = 0 ) {
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
		 * @param int $time
		 * @return bool
		 */
		$delete = function ( $key, $time = 0 ) use ( $cache ) {
			$cache->delete( $key, $time );
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
