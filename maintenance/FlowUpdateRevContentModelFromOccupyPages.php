<?php

namespace Flow\Maintenance;

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\IDBAccessObject;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * This script should be run immediately before dropping the wgFlowOccupyPages
 * configuration variable, to ensure that rev_content_model is set appropriately.
 *
 * See comments at https://gerrit.wikimedia.org/r/#/c/228267/ .
 *
 * It sets rev_content_model to flow-board for the last revision of all occupied pages.
 *
 * @ingroup Maintenance
 */
class FlowUpdateRevContentModelFromOccupyPages extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Sets rev_content_model from wgFlowOccupyPages, ' .
			'in preparation for dropping that config variable.' );

		$this->requireExtension( 'Flow' );

		// Given the number of occupied pages, this probably doesn't need to be
		// batched; just being cautious.
		$this->setBatchSize( 10 );
	}

	public function execute() {
		global $wgFlowOccupyPages;

		$dbw = $this->getPrimaryDB();

		$pageCount = count( $wgFlowOccupyPages );
		$overallInd = 0;
		$updatedCount = 0;
		$skippedCount = 0;
		$batchSize = $this->getBatchSize();

		while ( $overallInd < $pageCount ) {
			$this->beginTransaction( $dbw, __METHOD__ );
			$batchInd = 0;
			while ( $overallInd < $pageCount && $batchInd < $batchSize ) {
				$pageName = $wgFlowOccupyPages[$overallInd];
				$title = Title::newFromTextThrow( $pageName );
				$revId = $title->getLatestRevID( IDBAccessObject::READ_LATEST );
				if ( $revId !== 0 ) {
					$dbw->newUpdateQueryBuilder()
						->update( 'revision' )
						->set( [
							'rev_content_model' => CONTENT_MODEL_FLOW_BOARD
						] )
						->where( [ 'rev_id' => $revId ] )
						->caller( __METHOD__ )
						->execute();
					$updatedCount++;
					$this->output( "Set content model for \"{$title->getPrefixedDBkey()}\"\n" );
				} else {
					$skippedCount++;
					$this->output( "WARNING: Skipped \"{$title->getPrefixedDBkey()}\" because it does not exist\n" );
				}

				$overallInd++;
				$batchInd++;
			}

			$this->commitTransaction( $dbw, __METHOD__ );
			$this->output( "Completed batch.\n\n" );
		}

		$this->output( "Set content model for $updatedCount pages; skipped $skippedCount pages.\n" );
	}
}

$maintClass = FlowUpdateRevContentModelFromOccupyPages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
