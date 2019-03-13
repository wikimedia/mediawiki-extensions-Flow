<?php

namespace Flow\Import\LiquidThreadsApi;

use Iterator;
use ArrayIterator;

/**
 * Abstract class to store ID-indexed cached data.
 */
abstract class CachedData {
	protected $data = [];

	public function reset() {
		$this->data = [];
	}

	/**
	 * Get the value for a given ID
	 *
	 * @param int $id The ID to get
	 * @return mixed The data returned by retrieve()
	 */
	public function get( $id ) {
		$result = $this->getMulti( [ $id ] );
		return reset( $result );
	}

	public function getMaxId() {
		if ( $this->data ) {
			return max( array_keys( $this->data ) );
		} else {
			return 0;
		}
	}

	/**
	 * Get the value for a number of IDs
	 *
	 * @param int[] $ids List of IDs to retrieve
	 * @return array Associative array, indexed by ID.
	 */
	public function getMulti( array $ids ) {
		$this->ensureLoaded( $ids );

		$output = [];
		foreach ( $ids as $id ) {
			$output[$id] = $this->data[$id] ?? null;
		}

		return $output;
	}

	/**
	 * Gets the number of items stored in this object.
	 *
	 * @return int
	 */
	public function getSize() {
		return count( $this->data );
	}

	/**
	 * Uncached retrieval of data from the backend.
	 *
	 * @param int[] $ids The IDs to retrieve data for
	 * @return array Associative array of data retrieved, indexed by ID.
	 */
	abstract protected function retrieve( array $ids );

	/**
	 * Adds data to the object
	 *
	 * @param array $data Associative array, indexed by ID.
	 */
	protected function addData( array $data ) {
		$this->data += $data;
	}

	/**
	 * Load missing IDs from a list
	 *
	 * @param int[] $ids The IDs to retrieve
	 */
	protected function ensureLoaded( array $ids ) {
		$missing = array_diff( $ids, array_keys( $this->data ) );

		if ( count( $missing ) > 0 ) {
			$data = $this->retrieve( $missing );
			$this->addData( $data );
		}
	}
}

abstract class CachedApiData extends CachedData {
	protected $backend;

	public function __construct( ApiBackend $backend ) {
		$this->backend = $backend;
	}
}

/**
 * Cached LiquidThreads thread data.
 */
class CachedThreadData extends CachedApiData {
	protected $topics = [];

	protected function addData( array $data ) {
		parent::addData( $data );

		foreach ( $data as $thread ) {
			if ( self::isTopic( $thread ) ) {
				$this->topics[$thread['id']] = true;
			}
		}
		ksort( $this->topics );
	}

	/**
	 * Get the IDs of loaded threads that are top-level topics.
	 *
	 * @return array List of thread IDs in ascending order.
	 */
	public function getTopics() {
		return array_keys( $this->topics );
	}

	/**
	 * Create an iterator for the contained topic ids in ascending order
	 *
	 * @return Iterator<int>
	 */
	public function getTopicIdIterator() {
		return new ArrayIterator( $this->getTopics() );
	}

	/**
	 * Retrieve data for threads from the given page starting with the provided
	 * offset.
	 *
	 * @param string $pageName
	 * @param int $startId
	 * @return array Associative result array
	 */
	public function getFromPage( $pageName, $startId = 0 ) {
		$data = $this->backend->retrieveThreadData( [
			'thpage' => $pageName,
			'thstartid' => $startId
		] );
		$this->addData( $data );

		return $data;
	}

	protected function retrieve( array $ids ) {
		return $this->backend->retrieveThreadData( [
			'thid' => implode( '|', $ids ),
		] );
	}

	/**
	 * @param array $thread
	 * @return bool
	 */
	public static function isTopic( array $thread ) {
		return $thread['parent'] === null;
	}
}

/**
 * Cached MediaWiki page data.
 */
class CachedPageData extends CachedApiData {
	protected function retrieve( array $ids ) {
		return $this->backend->retrievePageDataById( $ids );
	}
}
