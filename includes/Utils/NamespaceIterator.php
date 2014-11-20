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
class NamespaceIterator implements IteratorAggregate {
	/**
	 * @var DatabaseBase
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $namespace;

	/**
	 * @param DatabaseBase $db
	 * @param int $namespace
	 */
	public function __construct( DatabaseBase $db, $namespace ) {
		$this->db = $db;
		$this->namespace = $namespace;
	}

	/**
	 * @return Iterator<Title>
	 */
	public function getIterator() {
		$it = new EchoBatchRowIterator(
			$this->db,
			/* tables */ array( 'page' ),
			/* pk */ 'page_id',
			/* rows per batch */ 500
		);
		$it->addConditions( array(
			'page_namespace' => $this->namespace,
		) );
		$it->setFetchColumns( array( 'page_title' ) );
		$it = new RecursiveIteratorIterator( $it );

		$namespace = $this->namespace;
		return new EchoCallbackIterator( $it, function( $row ) use ( $namespace ) {
			return Title::makeTitle( $namespace, $row->page_title );
		} );
	}
}
