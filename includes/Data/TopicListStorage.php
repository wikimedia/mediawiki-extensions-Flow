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
	public function insert( array $row ) {
		$row = parent::insert( $row );
		if ( $row !== false ) {
			$row['workflow_last_update_timestamp'] = wfTimestampNow();
		}
		return $row;
	}

}
