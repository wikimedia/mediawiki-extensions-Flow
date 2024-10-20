<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use Wikimedia\Rdbms\IDatabase;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

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

	private const TYPES = [
		'post' => PostRevision::class,
		'header' => Header::class,
		'post-summary' => PostSummary::class,
	];

	public function __construct() {
		parent::__construct();

		$this->setBatchSize( 300 );
		$this->requireExtension( 'Flow' );
	}

	protected function doDBUpdates() {
		$this->storage = $storage = Container::get( 'storage' );
		/** @var DbFactory $dbf */
		$dbf = Container::get( 'db.factory' );
		$dbw = $dbf->getDB( DB_PRIMARY );
		$fname = __METHOD__;

		$runUpdate = function ( $callback ) use ( $dbw, $storage, $fname ) {
			$continue = "\0";
			do {
				$this->beginTransaction( $dbw, $fname );
				$continue = $callback( $dbw, $continue );
				$this->commitTransaction( $dbw, $fname );
				$storage->clear();
			} while ( $continue !== null );
		};

		$runUpdate( [ $this, 'updateTreeRevision' ] );
		foreach ( [ 'rev_user', 'rev_mod_user', 'rev_edit_user' ] as $prefix ) {
			$runUpdate( function ( $dbw, $continue ) use ( $prefix ) {
				return $this->updateRevision( $prefix, $dbw, $continue );
			} );
		}

		return true;
	}

	public function updateTreeRevision( IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'tree_rev_id' ] )
			->from( 'flow_tree_revision' )
			->where( [
				$dbw->expr( 'tree_rev_id', '>', $continue ),
				$dbw->expr( 'tree_orig_user_ip', '!=', null ),
				$dbw->expr( 'tree_orig_user_id', '>', 0 ),
			] )
			->caller( __METHOD__ )
			->limit( $this->getBatchSize() )
			->orderBy( 'tree_rev_id' )
			->fetchResultSet();

		$om = Container::get( 'storage' )->getStorage( 'PostRevision' );
		$objs = $ids = [];
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
		$dbw->newUpdateQueryBuilder()
			->update( 'flow_tree_revision' )
			->set( [ 'tree_orig_user_ip' => null ] )
			->where( [ 'tree_rev_id' => $ids ] )
			->caller( __METHOD__ )
			->execute();
		foreach ( $objs as $obj ) {
			$om->cachePurge( $obj );
		}

		$this->completeCount += count( $ids );

		return end( $ids );
	}

	public function updateRevision( $columnPrefix, IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'rev_id', 'rev_type' ] )
			->from( 'flow_revision' )
			->where( [
				$dbw->expr( 'rev_id', '>', $continue ),
				$dbw->expr( "{$columnPrefix}_id", '>', 0 ),
				$dbw->expr( "{$columnPrefix}_ip", '!=', null ),
			] )
			->caller( __METHOD__ )
			->limit( $this->getBatchSize() )
			->orderBy( 'rev_id' )
			->fetchResultSet();

		$ids = $objs = [];
		foreach ( $rows as $row ) {
			$id = UUID::create( $row->rev_id );
			$type = self::TYPES[$row->rev_type];
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

		$dbw->newUpdateQueryBuilder()
			->update( 'flow_revision' )
			->set( [ "{$columnPrefix}_ip" => null ] )
			->where( [ 'rev_id' => $ids ] )
			->caller( __METHOD__ )
			->execute();

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

$maintClass = FlowFixUserIp::class;
require_once RUN_MAINTENANCE_IF_MAIN;
