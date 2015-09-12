<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

class PostRevisionBoardHistoryStorage extends BoardHistoryStorage {
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_topic_list', 'flow_tree_node', 'flow_tree_revision', 'flow_revision' ),
			array( '*' ),
			array(
				'rev_type' => 'post',
				'topic_id = tree_ancestor_id',
				'tree_descendant_id = tree_rev_descendant_id',
				'tree_rev_id = rev_id',
			) + $attributes,
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
