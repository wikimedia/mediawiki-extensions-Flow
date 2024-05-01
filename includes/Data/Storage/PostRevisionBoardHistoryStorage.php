<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;

class PostRevisionBoardHistoryStorage extends BoardHistoryStorage {
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
			->from( 'flow_topic_list' )
			->join( 'flow_tree_node', null, 'topic_id = tree_ancestor_id' )
			->join( 'flow_tree_revision', null, 'tree_descendant_id = tree_rev_descendant_id' )
			->join( 'flow_revision', null, 'tree_rev_id = rev_id' )
			->where( [ 'rev_type' => 'post' ] )
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
