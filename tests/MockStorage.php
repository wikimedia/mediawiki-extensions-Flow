<?php

namespace Flow\Data;

/**
 * Bare minimum ObjectStorage implementation does
 * nothing.  Query results must be passed in the constructor.
 *
 * Usage:
 *
 * 	$om = new MockObjectManager( 'rev_id', array(
 * 		array(
 * 			// query
 * 			array( 'rev_descendant_id' => 5 ),
 * 			// options
 * 			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 ),
 *			// results
 *			array(
 *				array( 'rev_id' => 22, 'rev_descendant_id' => 5, ... ),
 *			),
 *		),
 * 	) );
 */
class MockObjectManager implements ObjectManager {
	/**
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * @var array
	 */
	protected $results;

	public function __construct( $primaryKey, array $results ) {
		$this->primaryKey = $primaryKey;
		$this->results = $results;
	}

	public function find( $attributes, array $options = array() ) {
		// Slow for now, could be faster if it matters
		foreach ( $this->results as $meta ) {
			if ( $attributes === $meta[0] && $options === $meta[1] ) {
				return $meta[2];
			}
		}
		return null;
	}

	public function findMulti( array $queries, array $options = array() ) {
		$res = array();
		foreach ( $queries as $query ) {
			$res[] = $this->find( $query, $options );
		}
		return $res;
	}

	public function getPrimaryKeyColumns() {
		return $this->primaryKey;
	}

	public function clear() {}
	public function insert( array $row ) {}
	public function update( array $old, $new ) {}
	public function remove( array $row ) {}
}
