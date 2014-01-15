<?php

namespace Flow\Import;

abstract class CachedData {
	protected $data = array();

	public function get( $id ) {
		$result = $this->getMulti( array( $id ) );
		return reset( $result );
	}

	public function getMulti( array $ids ) {
		$this->ensureLoaded( $ids );

		$output = array();
		foreach( $ids as $id ) {
			$output[$id] = isset( $this->data[$id] ) ? $this->data[$id] : null;
		}

		return $output;
	}

	public function addData( array $data ) {
		$this->data += $data;
	}

	public function getSize() {
		return count( $this->data );
	}

	abstract protected function retrieve( array $ids );

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

	function __construct( LiquidThreadsApiBackend $backend ) {
		$this->backend = $backend;
	}
}

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

	public function getTopics() {
		return array_keys( $this->topics );
	}

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

class CachedPageData extends CachedApiData {
	protected function retrieve( array $ids ) {
		return $this->backend->retrievePageData( $ids );
	}
}