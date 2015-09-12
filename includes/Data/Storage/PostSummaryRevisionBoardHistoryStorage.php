<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

class PostSummaryRevisionBoardHistoryStorage extends BoardHistoryStorage {
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_revision', 'flow_topic_list', 'flow_tree_node' ),
			array( '*' ),
			array(
				'rev_type' => 'post-summary',
				'topic_id = rev_type_id',
			) + UUID::convertUUIDs( array( 'topic_list_id' => $attributes['topic_list_id'] ) ),
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
}