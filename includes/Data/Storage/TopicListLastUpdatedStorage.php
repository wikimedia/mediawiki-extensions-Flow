<?php

namespace Flow\Data\Storage;

use Flow\Exception\DataModelException;
use Flow\Model\UUID;

/**
 * Storage class for topic list ordered by last updated
 */
class TopicListLastUpdatedStorage extends TopicListStorage {

	/**
	 * Query topic list ordered by last updated field.  The sort field is in a
	 * different table so we need to overwrite parent find() method slightly to
	 * achieve this goal
	 *
	 * @param array $attributes
	 * @param array $options
	 * @return array
	 * @throws DataModelException
	 * @throws \MWException
	 */
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		if ( !$this->validateOptions( $options ) ) {
			throw new \MWException( "Validation error in database options" );
		}

		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		$res = $dbr->select(
			array( $this->table, 'flow_workflow' ),
			array( 'topic_list_id', 'topic_id', 'workflow_last_update_timestamp' ),
			array_merge( $attributes, array( 'topic_id = workflow_id' ) ),
			__METHOD__ . " ({$this->table})",
			$options
		);
		if ( $res === false ) {
			throw new DataModelException( __METHOD__ . ': Query failed: ' . $dbr->lastError(), 'process-data' );
		}

		$result = array();
		foreach ( $res as $row ) {
			$result[] = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
		}

		return $result;
	}

}
