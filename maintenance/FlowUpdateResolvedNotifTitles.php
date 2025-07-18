<?php
/**
 * Update the titles of flow-topic-resolved events to point to boards instead of topics
 *
 * @ingroup Maintenance
 */

namespace Flow\Maintenance;

use BatchRowIterator;
use Exception;
use Flow\Container;
use Flow\WorkflowLoaderFactory;
use MediaWiki\Extension\Notifications\DbFactory;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\Title\Title;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Maintenance script that update flow-topic-resolved events to point event_page_id to the board instead of the topic.
 *
 * @ingroup Maintenance
 */
class FlowUpdateResolvedNotifTitles extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();

		$this->addDescription( "Update the titles of flow-topic-resolved Echo events to point to boards instead of topics" );

		$this->setBatchSize( 500 );

		$this->requireExtension( 'Flow' );
	}

	public function getUpdateKey() {
		return 'FlowUpdateResolvedNotifTitles';
	}

	public function doDBUpdates() {
		$dbFactory = DbFactory::newFromDefault();
		$dbw = $dbFactory->getEchoDb( DB_PRIMARY );
		$dbr = $dbFactory->getEchoDb( DB_REPLICA );
		// We can't join echo_event with page, because those tables can be on different
		// DB clusters. If we had been able to do that, we could have added
		// wHERE page_namespace=NS_TOPIC, but instead we have to examine all rows
		// and skip the non-NS_TOPIC ones.
		$iterator = new BatchRowIterator(
			$dbr,
			'echo_event',
			'event_id',
			$this->getBatchSize()
		);
		$iterator->addConditions( [
			'event_type' => 'flow-topic-resolved',
			$dbr->expr( 'event_page_id', '!=', null ),
		] );
		$iterator->setFetchColumns( [ 'event_page_id' ] );
		$iterator->setCaller( __METHOD__ );

		$storage = Container::get( 'storage.workflow' );

		$this->output( "Retitling flow-topic-resolved events...\n" );

		$processed = 0;
		foreach ( $iterator as $batch ) {
			foreach ( $batch as $row ) {
				$topicTitle = Title::newFromID( $row->event_page_id );
				if ( !$topicTitle || $topicTitle->getNamespace() !== NS_TOPIC ) {
					continue;
				}
				$boardTitle = null;
				try {
					$uuid = WorkflowLoaderFactory::uuidFromTitle( $topicTitle );
					$workflow = $storage->get( $uuid );
					if ( $workflow ) {
						$boardTitle = $workflow->getOwnerTitle();
					}
				} catch ( Exception ) {
				}
				if ( $boardTitle ) {
					$dbw->newUpdateQueryBuilder()
						->update( 'echo_event' )
						->set( [ 'event_page_id' => $boardTitle->getArticleID() ] )
						->where( [ 'event_id' => $row->event_id ] )
						->caller( __METHOD__ )
						->execute();
					$processed += $dbw->affectedRows();
				} else {
					$this->output( "Could not find board for topic: " . $topicTitle->getPrefixedText() . "\n" );
				}
			}

			$this->output( "Updated $processed events.\n" );
			$this->waitForReplication();
		}

		return true;
	}
}

$maintClass = FlowUpdateResolvedNotifTitles::class;
require_once RUN_MAINTENANCE_IF_MAIN;
