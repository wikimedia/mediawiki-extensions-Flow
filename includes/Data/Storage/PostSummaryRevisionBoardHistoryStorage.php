<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

class PostSummaryRevisionBoardHistoryStorage extends BoardHistoryStorage {
	/**
	 * @param array $attributes
	 * @param array $options
	 * @return array
	 */
	public function find( array $attributes, array $options = [] ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		$dbr = $this->dbFactory->getDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'flow_revision', 'flow_topic_list', 'flow_tree_node' ],
			[ '*' ],
			array_merge( [
				'rev_type' => 'post-summary',
				'topic_id = tree_ancestor_id',
				'rev_type_id = tree_descendant_id',
			], $attributes ),
			__METHOD__,
			$options
		);

		$retval = [];
		foreach ( $res as $row ) {
			$row = UUID::convertUUIDs( (array)$row, 'alphadecimal' );
			$retval[$row['rev_id']] = $row;
		}

		return $retval;
	}
}
