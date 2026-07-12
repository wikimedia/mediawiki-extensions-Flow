<?php

namespace Flow;

use Wikimedia\Rdbms\DBReplicationWaitError;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\LBFactory;

/**
 * All classes within Flow that need to access the Flow db will go through
 * this class.  Having it separated into an object greatly simplifies testing
 * anything that needs to talk to the database.
 *
 * The factory receives, in its constructor, the wiki name and cluster name
 * that Flow-specific data is stored on.  Multiple wiki's can and should be
 * using the same wiki name and cluster to share Flow-specific data. These values
 * are used.
 *
 * There are also get getWikiLB and getWikiDB for the main wiki database.
 */
class DbFactory {
	/**
	 * @var string|false Wiki ID, or false for the current wiki
	 */
	protected $wiki;

	/**
	 * @var string|false External storage cluster, or false for core
	 */
	protected $cluster;

	/**
	 * @var bool When true only DB_PRIMARY will be returned
	 */
	protected $forcePrimary = false;

	/**
	 * @param LBFactory $lbFactory
	 * @param string|false $wiki Wiki ID, or false for the current wiki
	 * @param string|false $cluster External storage cluster, or false for core
	 */
	public function __construct(
		private readonly LBFactory $lbFactory,
		$wiki,
		$cluster,
	) {
		$this->wiki = $wiki;
		$this->cluster = $cluster;

		// Ensure maintenance scripts do not encounter replication errors
		if ( MW_ENTRY_POINT === 'cli' ) {
			$this->forcePrimary();
		}
	}

	public function forcePrimary() {
		$this->forcePrimary = true;
	}

	/**
	 * Gets a database connection for the Flow-specific database.
	 *
	 * @param int $db index of the connection to get.  DB_PRIMARY|DB_REPLICA.
	 * @return IDatabase|IReadableDatabase
	 */
	public function getDB( $db ) {
		return $this->getLB()->getConnection( $this->forcePrimary ? DB_PRIMARY : $db, [], $this->wiki );
	}

	/**
	 * Gets a load balancer for the Flow-specific database.
	 *
	 * @return \Wikimedia\Rdbms\ILoadBalancer
	 */
	private function getLB() {
		if ( $this->cluster !== false ) {
			return $this->lbFactory->getExternalLB( $this->cluster );
		} else {
			return $this->lbFactory->getMainLB( $this->wiki );
		}
	}

	/**
	 * Gets a database connection for the main wiki database.  Mockable version of wfGetDB.
	 *
	 * @param int $db index of the connection to get.  DB_PRIMARY|DB_REPLICA.
	 * @param string|bool $wiki The wiki ID, or false for the current wiki
	 * @return IDatabase|IReadableDatabase
	 */
	public function getWikiDB( $db, $wiki = false ) {
		return $this->lbFactory->getMainLB( $wiki )->getConnection( $this->forcePrimary ? DB_PRIMARY : $db, [], $wiki );
	}

	/**
	 * Wait for the replicas of the Flow database
	 */
	public function waitForReplicas() {
		try {
			$this->lbFactory->waitForReplication( [
				'wiki' => $this->wiki,
				'cluster' => $this->cluster,
				'ifWritesSince' => false
			] );
		} catch ( DBReplicationWaitError ) {
		}
	}

}
