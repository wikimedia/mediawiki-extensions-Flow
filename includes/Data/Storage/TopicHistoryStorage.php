<?php

namespace Flow\Data\Storage;

use Flow\Data\ObjectStorage;
use Flow\Exception\DataModelException;

/**
 * Query-only storage implementation merges PostRevision and
 * PostSummary instances to provide a full list of revisions for
 * a topics history.
 */
class TopicHistoryStorage implements ObjectStorage {

	/**
	 * @var ObjectStorage
	 */
	protected $postRevisionStorage;

	/**
	 * @var ObjectStorage
	 */
	protected $postSummaryStorage;

	/**
	 * @param ObjectStorage $postRevisionStorage
	 * @param ObjectStorage $postSummaryStorage
	 */
	public function __construct( ObjectStorage $postRevisionStorage, ObjectStorage $postSummaryStorage ) {
		$this->postRevisionStorage = $postRevisionStorage;
		$this->postSummaryStorage = $postSummaryStorage;
	}

	public function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( array( $attributes ), $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	public function findMulti( array $queries, array $options = array() ) {
		$data = $this->postRevisionStorage->findMulti( $queries, $options );
		$summary = $this->postSummaryStorage->findMulti( $queries, $options );
		if ( $summary ) {
			if ( $data ) {
				foreach ( $summary as $key => $rows ) {
					if ( isset( $data[$key] ) ) {
						$data[$key] += $rows;
						// Failing to sort is okay, we'd rather display unordered
						// result than showing an error page with exception
						krsort( $data[$key] );
					} else {
						$data[$key] = $rows;
					}
				}
			} else {
				$data = $summary;
			}
		}
		return $data;
	}

	public function getPrimaryKeyColumns() {
		return array( 'topic_root_id' );
	}

	public function insert( array $row ) {
		throw new DataModelException( __CLASS__ . ' does not support insert action', 'process-data' );
	}

	public function update( array $old, array $new ) {
		throw new DataModelException( __CLASS__ . ' does not support update action', 'process-data' );
	}

	public function remove( array $row ) {
		throw new DataModelException( __CLASS__ . ' does not support remove action', 'process-data' );
	}

	public function validate( array $row ) {
		return true;
	}
}
