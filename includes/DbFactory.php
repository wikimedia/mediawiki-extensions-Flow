<?php

namespace Flow;

/**
 * All classes within Flow that need to access the Flow db will go through here.
 *
 * To access core tables, use wfGetDB() etc. This is solely for Flow-specific
 * data, which may live on a separate database.
 */
class DbFactory {
	/**
	 * @var string|bool Wiki ID, or false for the current wiki
	 */
	protected $wiki;

	/**
	 * @var string|bool External storage cluster, or false for core
	 */
	protected $cluster;

	/**
	 * @var string|bool[optional] $wiki Wiki ID, or false for the current wiki
	 * @var string|bool[optional] $cluster External storage cluster, or false for core
	 */
	public function __construct( $wiki = false, $cluster = false ) {
		$this->wiki = $wiki;
		$this->cluster = $cluster;
	}

	/**
	 * @param int $db Index of the connection to get (DB_MASTER, DB_SLAVE or
	 * specific server index)
	 * @param mixed $groups Query groups
	 * @return \DatabaseBase
	 */
	public function getDB( $db, $groups = array() ) {
		return $this->getLB()->getConnection( $db, $groups, $this->wiki );
	}

	/**
	 * @return \LoadBalancer
	 */
	public function getLB() {
		if ( $this->cluster !== false ) {
			return wfGetLBFactory()->getExternalLB( $this->cluster, $this->wiki );
		} else {
			return wfGetLB( $this->wiki );
		}
	}
}
