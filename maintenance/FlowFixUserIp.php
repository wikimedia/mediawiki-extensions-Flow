<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Sets *_user_ip to null when *_user_id is > 0
 *
 * @ingroup Maintenance
 */
class FlowFixUserIp extends LoggedUpdateMaintenance {
	/**
	 * The number of entries completed
	 *
	 * @var int
	 */
	private $completeCount = 0;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	static private $types = array(
		'post' => 'Flow\Model\PostRevision',
		'header' => 'Flow\Model\Header',
		'post-summary' => 'Flow\Model\PostSummary',
	);

	protected function doDBUpdates() {
		$this->storage = $storage = Container::get( 'storage' );
		$dbf = Container::get( 'db.factory' );
		$dbw = $dbf->getDB( DB_MASTER );
		$hasRun = false;

		$runUpdate = function( $callback ) use ( $dbf, $dbw, $storage ) {
			$hasRun = true;
			$continue = "\0";
			do {
				$dbw->begin();
				$continue = call_user_func( $callback, $dbw, $continue );
				$dbw->commit();
				$dbf->waitForSlaves();
				$storage->clear();
			} while ( $continue !== null );
		};

		$runUpdate( array( $this, 'updateTreeRevision' ) );
		$self = $this;
		foreach ( array( 'rev_user', 'rev_mod_user', 'rev_edit_user' ) as $prefix ){
			$runUpdate( function( $dbw, $continue ) use ( $self, $prefix ) {
				return $self->updateRevision( $prefix, $dbw, $continue );
			} );
		}

		return true;
	}

	public function updateTreeRevision( DatabaseBase $dbw, $continue = null ) {
		$rows = $dbw->select(
			/* table */'flow_tree_revision',
			/* select */array( 'tree_rev_id' ),
			array(
				'tree_rev_id > ' . $dbw->addQuotes( $continue ),
				'tree_orig_user_ip IS NOT NULL',
				'tree_orig_user_id > 0',
			),
			__METHOD__,
			/* options */array( 'LIMIT' => $this->mBatchSize, 'ORDER BY' => 'tree_rev_id' )
		);

		$om = Container::get( 'storage' )->getStorage( 'PostRevision' );
		$objs = $ids = array();
		foreach ( $rows as $row ) {
			$id = UUID::create( $row->tree_rev_id );
			$found = $om->get( $id );
			if ( $found ) {
				$ids[] = $row->tree_rev_id;
				$objs[] = $found;
			} else {
				$this->error( __METHOD__ . ': Failed loading Flow\Model\PostRevision: ' . $id->getAlphadecimal() );
			}
		}
		if ( !$ids ) {
			return null;
		}
		$dbw->update(
			/* table */'flow_tree_revision',
			/* update */array( 'tree_orig_user_ip' => null ),
			/* conditions */array( 'tree_rev_id' => $ids ),
			__METHOD__
		);
		foreach ( $objs as $obj ) {
			$om->cachePurge( $obj );
		}

		$this->completeCount += count( $ids );

		return end( $ids );
	}

	public function updateRevision( $columnPrefix, DatabaseBase $dbw, $continue = null ) {
		$rows = $dbw->select(
			/* table */'flow_revision',
			/* select */array( 'rev_id', 'rev_type' ),
			/* conditions */ array(
				'rev_id > ' . $dbw->addQuotes( $continue ),
				"{$columnPrefix}_id > 0",
				"{$columnPrefix}_ip IS NOT NULL",
			),
			__METHOD__,
			/* options */array( 'LIMIT' => $this->mBatchSize, 'ORDER BY' => 'rev_id' )
		);

		$ids = $objs = array();
		foreach ( $rows as $row ) {
			$id = UUID::create( $row->rev_id );
			$type = self::$types[$row->rev_type];
			$om = $this->storage->getStorage( $type );
			$obj = $om->get( $id );
			if ( $obj ) {
				$om->merge( $obj );
				$ids[] = $row->rev_id;
				$objs[] = $obj;
			} else {
				$this->error( __METHOD__ . ": Failed loading $type: " . $id->getAlphadecimal() );
			}
		}
		if ( !$ids ) {
			return null;
		}

		$dbw->update(
			/* table */ 'flow_revision',
			/* update */ array( "{$columnPrefix}_ip" => null ),
			/* conditions */ array( 'rev_id' => $ids ),
			__METHOD__
		);

		foreach ( $objs as $obj ) {
			$this->storage->cachePurge( $obj );
		}

		$this->completeCount += count( $ids );

		return end( $ids );
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowFixUserIp';
	}
}

$maintClass = 'FlowFixUserIp'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
