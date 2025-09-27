<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use Flow\Container;
use Flow\DbFactory;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\WikiMap\WikiMap;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Adjusts edit counts for all existing Flow data.
 *
 * @ingroup Maintenance
 */
class FlowAddMissingModerationLogs extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		// phpcs:disable Generic.Files.LineLength
		$this->addDescription( 'Backfills missing moderation logs from flow_revision.  Must be run separately for each affected wiki.' );

		$this->addOption( 'start', 'rev_id of last moderation revision that was logged correctly before regression.', true, true );
		$this->addOption( 'stop', 'rev_id of first revision that was logged correctly after moderation logging fix.', true, true );
		// phpcs:enable

		$this->setBatchSize( 300 );

		$this->requireExtension( 'Flow' );
	}

	protected function getUpdateKey() {
		return 'FlowAddMissingModerationLogs';
	}

	protected function doDBUpdates() {
		$container = Container::getContainer();

		/** @var DbFactory $dbFactory */
		$dbFactory = $container['db.factory'];
		$dbw = $dbFactory->getDB( DB_PRIMARY );

		$storage = $container['storage'];

		$moderationLoggingListener = $container['storage.post.listeners.moderation_logging'];

		$rowIterator = new BatchRowIterator(
			$dbw,
			/* table = */'flow_revision',
			/* primary key = */'rev_id',
			$this->getBatchSize()
		);

		$rowIterator->setFetchColumns( [
			'rev_id',
			'rev_type',
		] );

		// Fetch rows that are a moderation action
		$rowIterator->addConditions( [
			'rev_change_type' => AbstractRevision::getModerationChangeTypes(),
			'rev_user_wiki' => WikiMap::getCurrentWikiId(),
		] );

		$start = $this->getOption( 'start' );
		$startId = UUID::create( $start );
		$rowIterator->addConditions( [
			$dbw->expr( 'rev_id', '>', $startId->getBinary() ),
		] );

		$stop = $this->getOption( 'stop' );
		$stopId = UUID::create( $stop );
		$rowIterator->addConditions( [
			$dbw->expr( 'rev_id', '<', $stopId->getBinary() ),
		] );

		$rowIterator->setCaller( __METHOD__ );

		$total = $fail = 0;
		foreach ( $rowIterator as $batch ) {
			$this->beginTransaction( $dbw, __METHOD__ );
			foreach ( $batch as $row ) {
				$total++;
				$objectManager = $storage->getStorage( $row->rev_type );
				$revId = UUID::create( $row->rev_id );
				$obj = $objectManager->get( $revId );
				if ( !$obj ) {
					$this->error( 'Could not load revision: ' . $revId->getAlphadecimal() );
					$fail++;
					continue;
				}

				$workflow = $obj->getCollection()->getWorkflow();
				$moderationLoggingListener->onAfterInsert( $obj, [], [
					'workflow' => $workflow,
				] );
			}

			$this->commitTransaction( $dbw, __METHOD__ );
			$storage->clear();
			$dbFactory->waitForReplicas();
		}

		$this->output( "Processed a total of $total moderation revisions.\n" );
		if ( $fail !== 0 ) {
			$this->error( "Errors were encountered while processing $fail of them.\n" );
		}

		return true;
	}
}

$maintClass = FlowAddMissingModerationLogs::class;
require_once RUN_MAINTENANCE_IF_MAIN;
