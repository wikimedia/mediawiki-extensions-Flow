<?php

use Flow\Container;
use Flow\Data\Listener\ModerationLoggingListener;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Adjusts edit counts for all existing Flow data.
 *
 * @ingroup Maintenance
 */
class FlowAddMissingModerationLogs extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Backfills missing moderation logs from flow_revision';

		$this->addOption( 'start', 'rev_id of last moderation revision that was logged correctly before regression.  Omit to backfill from the beginning', false, true );
		$this->addOption( 'stop', 'rev_id of first revision that was logged correctly after moderation logging fix.  Omit to backfill up to the current moment', false, true );

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return 'FlowAddMissingModerationLogs';
	}

	protected function doDBUpdates() {
		$container = Container::getContainer();

		$dbFactory = $container['db.factory'];
		$dbw = $dbFactory->getDb( DB_MASTER );

		$storage = $container['storage'];

		$moderationLoggingListener = $container['storage.post.listeners.moderation_logging'];

		$rowIterator = new EchoBatchRowIterator(
			$dbw,
			/* table = */'flow_revision',
			/* primary key = */'rev_id',
			$this->mBatchSize
		);

		$rowIterator->setFetchColumns( array(
			'rev_id',
			'rev_type',
		) );

		// Fetch rows that are a moderation action
		$rowIterator->addConditions( array(
			'rev_change_type' => ModerationLoggingListener::getModerationChangeTypes(),
		) );

		$start = $this->getOption( 'start' );
		if ( $start !== null ) {
			$startId = UUID::create( $start );
			$rowIterator->addConditions( array(
				'rev_id > ' . $dbw->addQuotes( $startId->getBinary() ),
			) );
		}

		$stop = $this->getOption( 'stop' );
		if ( $stop !== null ) {
			$stopId = UUID::create( $stop );
			$rowIterator->addConditions( array(
				'rev_id < ' . $dbw->addQuotes( $stopId->getBinary() ),
			) );
		}

		$total = $fail = 0;
		foreach ( $rowIterator as $batch ) {
			$dbw->begin();
			foreach ( $batch as $row ) {
				$total++;
				$classKey = "storage.{$row->rev_type}.class";
				$objectManager = $storage->getStorage( $container[$classKey] );
				$revId = UUID::create( $row->rev_id );
				$obj = $objectManager->get( $revId );
				if ( !$obj ) {
					$this->error( 'Could not load revision: ' . $revId->getAlphadecimal() );
					$fail++;
					continue;
				}

				$moderationLoggingListener->onAfterInsert( $obj, array(), array() );
			}

			$dbw->commit();
			$storage->clear();
			$dbFactory->waitForSlaves();
		}

		$this->output( "Processed a total of $total moderation revisions.\n" );
		if ( $fail !== 0 ) {
			$this->error( "Errors were encountered while processing $fail of them.\n" );
		}

		return true;
	}
}

$maintClass = 'FlowAddMissingModerationLogs';
require_once( RUN_MAINTENANCE_IF_MAIN );
