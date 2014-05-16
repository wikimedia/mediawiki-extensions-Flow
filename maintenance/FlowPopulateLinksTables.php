<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Currently iterates through all revisions for debugging purposes, the
 * production version will want to only process the most recent revision
 * of each object.
 *
 * @ingroup Maintenance
 */
class FlowPopulateLinksTables extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Populates links tables for wikis deployed before change 110090";
	}

	public function getUpdateKey() {
		return "FlowPopulateLinksTables";
	}

	public function doDBUpdates() {
		$this->output( "Populating links tables...\n" );
		$recorder = Container::get( 'reference.recorder' );
		$this->processHeaders( $recorder );
		$this->processPosts( $recorder );

		return true;
	}

	protected function processHeaders( $recorder ) {
		$storage = Container::get( 'storage.header' );
		$count = $this->mBatchSize;
		$id = '';
		$dbf = Container::get( 'db.factory' );
		$dbr = $dbf->getDB( DB_SLAVE );
		while ( $count === $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_revision' ),
				array( 'rev_type_id' ),
				array( 'rev_type' => 'header', 'rev_type_id > ' . $dbr->addQuotes( $id ) ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_type_id ASC', 'LIMIT' => $this->mBatchSize )
			);
			if ( !$res ) {
				throw new \MWException( 'SQL error in maintenance script ' . __METHOD__ );
			}
			foreach ( $res as $row ) {
				$count++;
				$id = $row->rev_type_id;
				$uuid = UUID::create( $id );
				$alpha = $uuid->getAlphadecimal();
				$header = $storage->get( $uuid );
				if ( $header ) {
					echo "Processing header $alpha\n";
					$recorder->onAfterInsert( $header, array() );
				}
			}
			$dbf->waitForSlaves();
		}
	}

	protected function processPosts( $recorder ) {
		$storage = Container::get( 'storage.post' );
		$count = $this->mBatchSize;
		$id = '';
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
		while ( $count === $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_tree_revision' ),
				array( 'tree_rev_id' ),
				array(
					'tree_parent_id IS NOT NULL',
					'tree_rev_id > ' . $dbr->addQuotes( $id ),
				),
				__METHOD__,
				array( 'ORDER BY' => 'tree_rev_id ASC', 'LIMIT' => $this->mBatchSize )
			);
			if ( !$res ) {
				throw new \MWException( 'SQL error in maintenance script ' . __METHOD__ );
			}
			foreach ( $res as $row ) {
				$count++;
				$id = $row->tree_rev_id;
				$uuid = UUID::create( $id );
				$alpha = $uuid->getAlphadecimal();
				$post = $storage->get( $uuid );
				if ( $post ) {
					echo "Processing post $alpha\n";
					$recorder->onAfterInsert( $post, array() );
				}
			}
		}
	}
}

$maintClass = "FlowPopulateLinksTables";
require_once( RUN_MAINTENANCE_IF_MAIN );
