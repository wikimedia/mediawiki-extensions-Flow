<?php

namespace Flow\Data;

/**
 * Storage class for topic list ordered by last updated
 */
class TopicListStorage extends BasicDbStorage {

	/**
	 * We need workflow_last_update_timestamp for updating
	 * the ordering in cache
	 */
	public function insert( array $rows ) {
		$updateRows = array();
		foreach ( $rows as $i => $row ) {
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
