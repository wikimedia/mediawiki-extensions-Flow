<?php
/**
 * Update the titles of flow-topic-resolved notifications to point to boards instead of topics
 *
 * @ingroup Maintenance
 */

use Flow\Container;
use Flow\WorkflowLoaderFactory;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: __DIR__ . '/../../../maintenance/Maintenance.php' );

/**
 * Maintenance script that removes orphaned event rows
 *
 * @ingroup Maintenance
 */
class FlowUpdateResolvedNotifTitles extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();

		$this->mDescription = "Update the titles of flow-topic-resolved notifications to point to boards instead of topics";

		$this->setBatchSize( 500 );
	}

	public function getUpdateKey() {
		return __CLASS__;
	}

	public function doDBUpdates() {
		$dbFactory = MWEchoDbFactory::newFromDefault();
		$dbw = $dbFactory->getEchoDb( DB_MASTER );
		$dbr = $dbFactory->getEchoDb( DB_SLAVE );
		$iterator = new BatchRowIterator(
			$dbr,
			'echo_event',
			'event_id',
			$this->mBatchSize
		);
		$iterator->addConditions( array(
			'event_type' => 'flow-topic-resolved',
			'event_page_id IS NOT NULL',
		) );
		$iterator->setFetchColumns( array( 'event_page_id' ) );

		$storage = Container::get( 'storage.workflow' );

		$this->output( "Retitling flow-topic-resolved notifications...\n" );

		$processed = 0;
		foreach ( $iterator as $batch ) {
			foreach ( $batch as $row ) {
				$topicTitle = Title::newFromId( $row->event_page_id );
				if ( $topicTitle->getNamespace() !== NS_TOPIC ) {
					continue;
				}
				$boardTitle = null;
				try {
					$uuid = WorkflowLoaderFactory::uuidFromTitle( $topicTitle );
					$workflow = $storage->get( $uuid );
					if ( $workflow ) {
						$boardTitle = $workflow->getOwnerTitle();
					}
				} catch ( Exception $e ) {}
				if ( $boardTitle ) {
					$dbw->update(
						'echo_event',
						array( 'event_page_id' => $boardTitle->getArticleId() ),
						array( 'event_id' => $row->event_id )
					);
					$processed += $dbw->affectedRows();
				} else {
					$this->output( "Could not find board for topic: " . $topicTitle->getPrefixedText() . "\n" );
				}
			}

			$this->output( "Updated $processed notifications.\n" );
			$dbFactory->waitForSlaves();
		}

		return true;
	}
}

$maintClass = 'FlowUpdateResolvedNotifTitles';
require_once ( RUN_MAINTENANCE_IF_MAIN );
