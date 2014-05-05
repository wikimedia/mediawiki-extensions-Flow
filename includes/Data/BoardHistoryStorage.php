<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\Exception\DataModelException;

class BoardHistoryStorage extends DbStorage {

	function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( $attributes, $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) > 1 ) {
			throw new DataModelException( __METHOD__ . ' expects only one value in $queries', 'process-data' );
		}
		$merged = $this->findHeaderHistory( $queries, $options ) +
			$this->findTopicListHistory( $queries, $options ) +
			$this->findTopicSummaryHistory( $queries, $options );
		// newest items at the begining of the list
		krsort( $merged );
		return RevisionStorage::mergeExternalContent( array( $merged ) );
	}

	function findHeaderHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_revision' ),
			array( '*' ),
			array( 'rev_type' => 'header' ) + UUID::convertUUIDs( array( 'rev_type_id' => $queries['topic_list_id'] ) ),
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$row = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
				$retval[$row['rev_id']] = $row;
			}
		}
		return $retval;
	}

	function findTopicSummaryHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_revision', 'flow_topic_list', 'flow_tree_node' ),
			array( '*' ),
			array(
				'rev_type' => 'post-summary',
				'topic_id = tree_ancestor_id',
				'rev_type_id = tree_descendant_id'
			) + UUID::convertUUIDs( array( 'topic_list_id' => $queries['topic_list_id'] ) ),
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$row = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
				$retval[$row['rev_id']] = $row;
			}
		}
		return $retval;
	}

	function findTopicListHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_topic_list', 'flow_tree_node', 'flow_tree_revision', 'flow_revision' ),
			array( '*' ),
			array(
				'topic_id = tree_ancestor_id',
				'tree_descendant_id = tree_rev_descendant_id',
				'tree_rev_id = rev_id',
			) + $queries,
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$row = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
				$retval[$row['rev_id']] = $row;
			}
		}
		return $retval;
	}

	public function getPrimaryKeyColumns() {
		return array( 'topic_list_id' );
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
