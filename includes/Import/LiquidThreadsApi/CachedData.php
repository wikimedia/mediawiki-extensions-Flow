<?php

namespace Flow\Import\LiquidThreadsApi;

/**
 * Abstract class to store ID-indexed cached data.
 */
abstract class CachedData {
	protected $data = array();

	/**
	 * Get the value for a given ID
	 * @param  int $id The ID to get
	 * @return mixed     The data returned by retrieve()
	 */
	public function get( $id ) {
		$result = $this->getMulti( array( $id ) );
		return reset( $result );
	}

	/**
	 * Get the value for a number of IDs
	 * @param  array  $ids List of IDs to retrieve
	 * @return array      Associative array, indexed by ID.
	 */
	public function getMulti( array $ids ) {
		$this->ensureLoaded( $ids );

		$output = array();
		foreach( $ids as $id ) {
			$output[$id] = isset( $this->data[$id] ) ? $this->data[$id] : null;
		}

		return $output;
	}

	/**
	 * Adds data to the object
	 * @param array $data Associative array, indexed by ID.
	 */
	public function addData( array $data ) {
		$this->data += $data;
	}

	/**
	 * Gets the number of items stored in this object.
	 * @return int
	 */
	public function getSize() {
		return count( $this->data );
	}

	/**
	 * Uncached retrieval of data from the backend.
	 * @param  array  $ids The IDs to retrieve data for
	 * @return array      Associative array of data retrieved, indexed by ID.
	 */
	abstract protected function retrieve( array $ids );

	/**
	 * Load missing IDs from a list
	 * @param  array  $ids The IDs to retrieve
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

	function __construct( ApiBackend $backend ) {
		$this->backend = $backend;
	}
}

/**
 * Cached LiquidThreads thread data.
 */
class CachedThreadData extends CachedApiData {
	protected $topics = array();


	public function addData( array $data ) {
		parent::addData( $data );

		foreach( $data as $thread ) {
			if ( $thread['parent'] === null ) {
				$this->topics[$thread['id']] = true;
			}
		}
	}

	/**
	 * Get the IDs of loaded threads that are top-level topics.
	 * @return array List of thread IDs.
	 */
	public function getTopics() {
		return array_keys( $this->topics );
	}

	/**
	 * Retrieve data for threads with the given API parameters as search criteria.
	 * @param  array  $params Associative array of parameters to pass to the LQT API module.
	 * @return array         Associative result array
	 */
	public function getFromParams( array $params ) {
		$data = $this->retrieveFromParams( $params );

		$this->addData( $data );

		return $data;
	}

	protected function retrieve( array $ids ) {
		return $this->retrieveFromParams( array(
			'thid' => implode( '|', $ids ),
		) );
	}

	protected function retrieveFromParams( array $params ) {
		return $this->backend->retrieveThreadData( $params );
	}
}

/**
 * Cached MediaWiki page data.
 */
class CachedPageData extends CachedApiData {
	protected function retrieve( array $ids ) {
		return $this->backend->retrievePageData( $ids );
	}
}
