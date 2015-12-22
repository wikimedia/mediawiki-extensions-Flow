<?php

namespace Flow\Utils;

use DatabaseBase;
use BatchRowIterator;
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
	 * Page id to start at (inclusive)
	 *
	 * @var int|null
	 */
	protected $startId = null;

	/**
	 * Page id to stop at (exclusive)
	 *
	 * @var int|null
	 */
	protected $stopId = null;

	/**
	 * @param DatabaseBase $db
	 * @param string $propName
	 * @param int|null $startId Page id to start at (inclusive)
	 * @param int|null $stopId Page id to stop at (exclusive)
	 */
	public function __construct( DatabaseBase $db, $propName, $startId = null, $stopId = null ) {
		$this->db = $db;
		$this->propName = $propName;
		$this->startId = $startId;
		$this->stopId = $stopId;
	}

	/**
	 * @return Iterator<Title>
	 */
	public function getIterator() {
		$it = new BatchRowIterator(
			$this->db,
			/* tables */ array( 'page_props', 'page' ),
			/* pk */ 'pp_page',
			/* rows per batch */ 500
		);

		$conditions = array( 'pp_propname' => $this->propName );
		if ( $this->startId !== null ) {
			$conditions[] = 'pp_page >= ' . $this->db->addQuotes( $this->startId );
		}
		if ( $this->stopId !== null ) {
			$conditions[] = 'pp_page < ' . $this->db->addQuotes( $this->stopId );
		}
		$it->addConditions( $conditions );

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
