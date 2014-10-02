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
			if ( $result === false ) {
				return array();
			}

			$values += $result;
		}

		// The memcached BagOStuff returns only existing keys, but the redis
		// BagOStuff puts a false for all keys it doesn't find.  Resolve that
		// inconsistency here by filtering all false values
		return array_filter( $values, function( $value ) {
			return $value !== false;
		} );

		return $values;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param int $exptime
	 * @return bool
	 */
	public function set( $key, $value, $exptime = 0 ) {
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args() );

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
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args() );

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
		$self = $this;
		$cache = $this->cache;

		/**
		 * Since our add is deferred, the condition for add (value should not
		 * yet exist) may have changed. In this case, we'll want to just delete
		 * that key from memory, because it can't be trusted anymore.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @param int $exptime
		 * @return bool
		 */
		$add = function ( $key, $value, $exptime = 0 ) use ( $cache, $self ) {
			$success = $cache->add( $key, $value, $exptime );

			// If we failed to add the value, clear the (now incorrect) value
			// from buffered cache
			if ( !$success ) {
				$cache->delete( $key );
				$self->clear( $key );
			}

			return $success;
		};

		if ( $this->get( $key ) === false ) {
			$this->defer( $add, func_get_args() );

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
	public function cas( $casToken, $key, $value, $exptime = 0 ) {
		$self = $this;
		$cache = $this->cache;
		$originalValue = isset( $this->casTokens[$casToken] ) ? $this->casTokens[$casToken] : null;

		/**
		 * @param mixed $casToken
		 * @param string $key
		 * @param mixed $value
		 * @param int $exptime
		 * @return bool
		 */
		$cas = function ( $casToken, $key, $value, $exptime = 0 ) use ( $cache, $originalValue, $self ) {
			// Check if given (local) CAS token was known
			if ( $originalValue === null ) {
				return false;
			}

			// Fetch data from real cache, getting new valid CAS token
			$current = $cache->get( $key, $casToken );

			// Check if the value we just read from real cache is still the same
			// as the one we saved when doing the original fetch
			$success = false;
			if ( serialize( $current ) === $originalValue ) {
				// Everything still checked out, let's CAS the value for real now
				$success = $cache->cas( $casToken, $key, $value, $exptime );
			}

			// If CAS failed now, let's just delete this cache key (both in
			// local buffer and in real cache), it can't be trusted anymore
			if ( !$success ) {
				$cache->delete( $key );
				$self->clear( $key );
			}

			return $success;
		};

		// CAS value to local cache/memory
		$success = false;
		if ( serialize( $this->get( $key ) ) === $originalValue ) {
			$success = parent::set( $key, $value, $exptime );
		}

		// Only schedule the CAS to be performed on real cache if it was OK on
		// local cache
		if ( $success ) {
			$this->defer( $cas, func_get_args() );
		}

		return $success;
	}

	/**
	 * @param string $key
	 * @param int $time
	 * @return bool
	 */
	public function delete( $key, $time = 0 ) {
		$this->defer( array( $this->cache, __FUNCTION__ ), func_get_args() );

		// Check the current value to see if is currently exists, so we can
		// properly return true/false as would be expected from other BagOStuff
		$value = $this->get( $key );

		// To make sure that subsequent get() calls for this key don't return
		// a value (it's supposed to be deleted), we'll make it is expired in
		// our temporary bag.
		parent::set( $key, '', time() - 1 );

		return $value !== false;
	}

	/**
	 * This will delete a key from our local cache for real (which will make
	 * follow-up get requests retrieve the value from real cache again)
	 * This should only be used when there was a problem committing data to
	 * real cache, at which point our buffered cache may be incorrect.
	 *
	 * @param string $key
	 * @return bool
	 */
	protected function clear( $key ) {
		return parent::delete( $key );
	}

	/**
	 * @param callable $callback
	 * @param array $arguments
	 */
	protected function defer( $callback, $arguments ) {
		$this->buffer[] = array( $callback, $arguments );

		// persist to real cache immediately, if we're not in a "transaction"
		if ( !$this->transaction ) {
			$this->commit();
		}
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
	 */
	public function commit() {
		foreach ( $this->buffer as $update ) {
			call_user_func_array( $update[0], $update[1] );
		}

		// data has been persisted to real cache, now clear everything local
		$this->rollback();
	}

	/**
	 * Roll back all scheduled changes.
	 */
	public function rollback() {
		$this->bag = array();
		$this->buffer = array();
		$this->transaction = false;
	}
}
