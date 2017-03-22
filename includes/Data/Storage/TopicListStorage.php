<?php

namespace Flow\Data\Storage;

/**
 * Storage class for topic list ordered by last updated
 */
class TopicListStorage extends BasicDbStorage {

	protected function doFindQuery( array $preprocessedAttributes, array $options = array() ) {
		return $this->dbFactory->getDB( DB_SLAVE )->select(
			array( $this->table, 'flow_workflow' ),
			array( 'topic_list_id', 'topic_id', 'workflow_last_update_timestamp' ),
			$preprocessedAttributes,
			__METHOD__ . " ({$this->table})",
			$options,
			array( 'flow_workflow' => array( 'INNER JOIN', 'workflow_id = topic_id' ) )
		);
	}

	/**
	 * We need workflow_last_update_timestamp for updating
	 * the ordering in cache
	 */
	public function insert( array $rows ) {
		$updateRows = array();
		foreach ( $rows as $i => $row ) {
			// Note, entries added directly to the index (rather than from DB
			// fill) do have this key, but obviously it can't be used.
			unset( $row['workflow_last_update_timestamp'] );
			$updateRows[$i] = $row;
		}
		$res = parent::insert( $updateRows );
		if ( $res ) {
			return $rows;
		} else {
			return false;
		}
	}

}
