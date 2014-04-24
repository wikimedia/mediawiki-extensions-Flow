<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\Exception\DataModelException;

class TopicHistoryStorage implements ObjectStorage {

	protected $postRevisionStorage;

	protected $postSummaryStorage;

	public function __construct( $postRevisionStorage, $postSummaryStorage ) {
		$this->postRevisionStorage = $postRevisionStorage;
		$this->postSummaryStorage = $postSummaryStorage;
	}

	public function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( $attributes, $options );
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

	public function getIterator() {
		throw new DataModelException( 'Not Implemented', 'process-data' );
	}

}
