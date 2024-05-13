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
		$res = $dbr->newSelectQueryBuilder()
			->select( '*' )
			->from( 'flow_revision' )
			->join( 'flow_tree_node', null, 'rev_type_id = tree_descendant_id' )
			->join( 'flow_topic_list', null, 'topic_id = tree_ancestor_id' )
			->where( [ 'rev_type' => 'post-summary' ] )
			->andWhere( $attributes )
			->options( $options )
			->caller( __METHOD__ )
			->fetchResultSet();

		$retval = [];
		foreach ( $res as $row ) {
			$row = UUID::convertUUIDs( (array)$row, 'alphadecimal' );
			$retval[$row['rev_id']] = $row;
		}

		return $retval;
	}
}
