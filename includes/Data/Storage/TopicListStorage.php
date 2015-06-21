<?php

namespace Flow\Data\Storage;

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
