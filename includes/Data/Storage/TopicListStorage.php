<?php

namespace Flow\Data\Storage;

/**
 * Storage class for topic list ordered by last updated
 */
class TopicListStorage extends BasicDbStorage {

	protected function doFindQuery( array $preprocessedAttributes, array $options = [] ) {
		return $this->dbFactory->getDB( DB_REPLICA )->newSelectQueryBuilder()
			->select( [ 'topic_list_id', 'topic_id', 'workflow_last_update_timestamp' ] )
			->from( $this->table )
			->join( 'flow_workflow', null, 'workflow_id = topic_id' )
			->where( $preprocessedAttributes )
			->caller( __METHOD__ . " ({$this->table})" )
			->options( $options )
			->fetchResultSet();
	}

	/**
	 * We need workflow_last_update_timestamp for updating
	 * the ordering in cache
	 * @param array $rows
	 * @return array|false
	 */
	public function insert( array $rows ) {
		$updateRows = [];
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
