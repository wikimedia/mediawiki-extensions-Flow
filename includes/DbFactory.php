<?php

namespace Flow;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\DBReplicationWaitError;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\ILoadBalancer;

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
	 * @var string|bool Wiki ID, or false for the current wiki
	 */
	protected $wiki;

	/**
	 * @var string|bool External storage cluster, or false for core
	 */
	protected $cluster;

	/**
	 * @var bool When true only DB_PRIMARY will be returned
	 */
	protected $forcePrimary = false;

	/**
	 * @param string|bool $wiki Wiki ID, or false for the current wiki
	 * @param string|bool $cluster External storage cluster, or false for core
	 */
	public function __construct( $wiki = false, $cluster = false ) {
		$this->wiki = $wiki;
		$this->cluster = $cluster;
	}

	public function forcePrimary() {
		$this->forcePrimary = true;
	}

	/**
	 * Gets a database connection for the Flow-specific database.
	 *
	 * @param int $db index of the connection to get.  DB_PRIMARY|DB_REPLICA.
	 * @return IDatabase
	 */
	public function getDB( $db ) {
		return $this->getLB()->getConnection( $this->forcePrimary ? DB_PRIMARY : $db, [], $this->wiki );
	}

	/**
	 * Gets a load balancer for the Flow-specific database.
	 *
	 * @return \Wikimedia\Rdbms\ILoadBalancer
	 */
	public function getLB() {
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		if ( $this->cluster !== false ) {
			return $lbFactory->getExternalLB( $this->cluster );
		} else {
			return $lbFactory->getMainLB( $this->wiki );
		}
	}

	/**
	 * Gets a database connection for the main wiki database.  Mockable version of wfGetDB.
	 *
	 * @param int $db index of the connection to get.  DB_PRIMARY|DB_REPLICA.
	 * @param string|bool $wiki The wiki ID, or false for the current wiki
	 * @return IDatabase
	 */
	public function getWikiDB( $db, $wiki = false ) {
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		return $lbFactory->getMainLB( $wiki )->getConnection( $this->forcePrimary ? DB_PRIMARY : $db, [], $wiki );
	}

	/**
	 * Gets a load balancer for the main wiki database.
	 *
	 * @param string|bool $wiki wiki ID, or false for the current wiki
	 * @return ILoadBalancer
	 */
	public function getWikiLB( $wiki = false ) {
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		return $lbFactory->getMainLB( $wiki );
	}

	/**
	 * Wait for the replicas of the Flow database
	 */
	public function waitForReplicas() {
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		try {
			$lbFactory->waitForReplication( [
				'wiki' => $this->wiki,
				'cluster' => $this->cluster,
				'ifWritesSince' => false
			] );
		} catch ( DBReplicationWaitError $e ) {
		}
	}

	/**
	 * Roll back changes on all databases.
	 * @see LBFactory::rollbackPrimaryChanges
	 * @param string $fname
	 */
	public function rollbackPrimaryChanges( $fname = __METHOD__ ) {
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$lbFactory->rollbackPrimaryChanges( $fname );
	}
}
