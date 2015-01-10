<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

/**
 * Storage class for topic list ordered by last updated
 */
class TopicListLastUpdatedStorage extends TopicListStorage {

	/**
	 * Query topic list ordered by last updated field.  The sort field is in a
	 * different table so we need to overwrite parent find() method slightly to
	 * achieve this goal
	 */
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		if ( !$this->validateOptions( $options ) ) {
			throw new \Exception( "Validation error in database options" );
		}

		$res = $this->dbFactory->getDB( DB_MASTER )->select(
			array( $this->table, 'flow_workflow' ),
			'topic_list_id, topic_id, workflow_last_update_timestamp',
			array_merge( $attributes, array( 'topic_id = workflow_id' ) ),
			__METHOD__ . " ({$this->table})",
			$options
		);
		if ( ! $res ) {
			// TODO: This should probably not silently fail on database errors.
			return null;
		}

		$result = array();
		foreach ( $res as $row ) {
			$result[] = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
		}

		return $result;
	}

}
