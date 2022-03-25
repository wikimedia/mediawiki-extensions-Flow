<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\DbFactory;
use Flow\Model\UUID;
use LoggedUpdateMaintenance;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

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
		$this->addDescription( "Populates links tables for wikis deployed before change 110090" );
		$this->setBatchSize( 300 );
		$this->requireExtension( 'Flow' );
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
		$batchSize = $this->getBatchSize();
		$count = $batchSize;
		$id = '';
		/** @var DbFactory $dbf */
		$dbf = Container::get( 'db.factory' );
		$dbr = $dbf->getDB( DB_REPLICA );
		while ( $count === $batchSize ) {
			$count = 0;
			$res = $dbr->select(
				[ 'flow_revision' ],
				[ 'rev_type_id' ],
				[ 'rev_type' => 'header', 'rev_type_id > ' . $dbr->addQuotes( $id ) ],
				__METHOD__,
				[ 'ORDER BY' => 'rev_type_id ASC', 'LIMIT' => $batchSize ]
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
					$recorder->onAfterInsert(
						$header, [],
						[
							'workflow' => $header->getCollection()->getWorkflow()
						]
					);
				}
			}
			$dbf->waitForReplicas();
		}
	}

	protected function processPosts( $recorder ) {
		$storage = Container::get( 'storage.post' );
		$batchSize = $this->getBatchSize();
		$count = $batchSize;
		$id = '';
		$dbr = Container::get( 'db.factory' )->getDB( DB_REPLICA );
		while ( $count === $batchSize ) {
			$count = 0;
			$res = $dbr->select(
				[ 'flow_tree_revision' ],
				[ 'tree_rev_id' ],
				[
					'tree_parent_id IS NOT NULL',
					'tree_rev_id > ' . $dbr->addQuotes( $id ),
				],
				__METHOD__,
				[ 'ORDER BY' => 'tree_rev_id ASC', 'LIMIT' => $batchSize ]
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
					$recorder->onAfterInsert(
						$post, [],
						[
							'workflow' => $post->getCollection()->getWorkflow()
						]
					);
				}
			}
		}
	}
}

$maintClass = FlowPopulateLinksTables::class;
require_once RUN_MAINTENANCE_IF_MAIN;
