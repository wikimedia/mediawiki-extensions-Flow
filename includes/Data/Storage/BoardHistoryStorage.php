<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;
use Flow\Exception\DataModelException;

/**
 * SQL backing for BoardHistoryIndex fetches revisions related
 * to a specific TopicList(board workflow)
 */
class BoardHistoryStorage extends DbStorage {

	public function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( array( $attributes ), $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	public function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) > 1 ) {
			throw new DataModelException( __METHOD__ . ' expects only one value in $queries', 'process-data' );
		}

		$merged = $this->findHeaderHistory( $queries, $options ) +
			$this->findTopicListHistory( $queries, $options ) +
			$this->findTopicSummaryHistory( $queries, $options );

		// Having merged data from 3 sources, we now have to combine it
		// (according to the current sort & limit)
		$order = isset( $options['ORDER BY'][0] ) && preg_match( '/ASC$/', $options['ORDER BY'][0] ) ? 'ASC' : 'DESC';
		if ( $order === 'DESC' ) {
			krsort( $merged );
		} else {
			ksort( $merged );
		}

		if ( isset( $options['LIMIT'] ) ) {
			$merged = array_splice( $merged, 0, $options['LIMIT'] );
		}

		// Merge data from external store & get rid of failures
		$res = array( $merged );
		$res = RevisionStorage::mergeExternalContent( $res );
		foreach ( $res as $i => $result ) {
			if ( $result ) {
				$res[$i] = array_filter( $result, array( $this, 'validate' ) );
			}
		}

		return $res;
	}

	protected function findHeaderHistory( array $queries, array $options = array() ) {
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

	protected function findTopicSummaryHistory( array $queries, array $options = array() ) {
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

	protected function findTopicListHistory( array $queries, array $options = array() ) {
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

	/**
	 * When retrieving revisions from DB, RevisionStorage::mergeExternalContent
	 * will be called to fetch the content. This could fail, resulting in the
	 * content being a 'false' value.
	 *
	 * {@inheritDoc}
	 */
	public function validate( array $row ) {
		return !isset( $row['rev_content'] ) || $row['rev_content'] !== false;
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
}
