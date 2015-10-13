<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

class PostSummaryRevisionBoardHistoryStorage extends BoardHistoryStorage {
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_revision', 'flow_topic_list', 'flow_tree_node' ),
			array( '*' ),
			array_merge( array(
				'rev_type' => 'post-summary',
				'topic_id = tree_ancestor_id',
				'rev_type_id = tree_descendant_id',
			), $attributes ),
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