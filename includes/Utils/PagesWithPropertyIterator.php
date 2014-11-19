<?php

namespace Flow\Utils;

use DatabaseBase;
use EchoBatchRowIterator;
use EchoCallbackIterator;
use IteratorAggregate;
use RecursiveIteratorIterator;
use Title;

/**
 * Iterates over all titles that have the specified page property
 */
class PagesWithPropertyIterator implements IteratorAggregate {
	/**
	 * @var DatabaseBase
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $propName;

	/**
	 * @param DatabaseBase $db
	 * @param string $propName
	 */
	public function __construct( DatabaseBase $db, $propName ) {
		$this->db = $db;
		$this->propName = $propName;
	}

	/**
	 * @return Iterator<Title>
	 */
	public function getIterator() {
		$it = new EchoBatchRowIterator(
			$this->db,
			/* tables */ array( 'page_props', 'page' ),
			/* pk */ 'pp_page',
			/* rows per batch */ 500
		);
		$it->addConditions( array(
			'pp_propname' => $this->propName
		) );
		$it->addJoinConditions( array(
			'page' => array( 'JOIN', 'pp_page=page_id' ),
		) );
		$it->setFetchColumns( array( 'page_namespace', 'page_title' ) );
		$it = new RecursiveIteratorIterator( $it );

		return new EchoCallbackIterator( $it, function( $row ) {
			return Title::makeTitle( $row->page_namespace, $row->page_title );
		} );
	}
}
