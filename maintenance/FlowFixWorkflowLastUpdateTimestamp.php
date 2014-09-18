<?php

use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Search\Updater;
use Flow\Search\TopicUpdater;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * @ingroup Maintenance
 */
class FlowFixWorkflowLastUpdateTimestamp extends Maintenance {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ObjectManager
	 */
	protected $storage;

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Fixes any incorrect workflow_last_update_timestamp for topics';

		$this->setBatchSize( 10 );
	}

	public function execute() {
		$this->dbFactory = Container::get( 'db.factory' );
		$this->storage = Container::get( 'storage' )->getStorage( 'Workflow' );

		/** @var Updater[] $updaters */
		$updaters = Container::get( 'searchindex.updaters' );
		/** @var TopicUpdater $topicUpdater */
		$topicUpdater = $updaters['topic'];

		$conditions = array();
		while ( true ) {
			$workflows = $topicUpdater->getWorkflows( $conditions, array( 'LIMIT' => $this->mBatchSize ) );

			if ( !$workflows ) {
				// break on query error
				break;
			}

			$roots = $topicUpdater->getRoots( $workflows );

			// loop all workflows, match then to the topic PostRevision object &
			// recursively get the last update timestamp from all of the topic's
			// children
			$matches = false;
			$uuids = array();
			$timestamps = array();
			foreach ( $workflows as $workflow ) {
				$matches = true;
				$uuid = UUID::create( $workflow->workflow_id );
				$root = $roots[$uuid->getAlphadecimal()];

				$timestamp = $topicUpdater->getUpdateTimestamp( $root )->getTimestamp( TS_MW );
				if ( $timestamp != $workflow->workflow_last_update_timestamp ) {
					$uuids[$uuid->getAlphadecimal()] = $uuid;
					$timestamps[$uuid->getAlphadecimal()] = $timestamp;
				}

				// update query conditions for next batch
				$next = wfTimestamp( TS_UNIX, $workflow->workflow_last_update_timestamp ) + 1;
				$next = wfTimestamp( TS_MW, $next );
				$dbr = $this->dbFactory->getDB( DB_SLAVE );
				$conditions = array( 'workflow_last_update_timestamp > ' . $dbr->addQuotes( $next ) );
			}

			if ( !$matches ) {
				// no more workflows, we're done
				break;
			}

			if ( $uuids ) {
				$this->updateWorkflows( $uuids, $timestamps );
				$this->dbFactory->waitForSlaves();
			}
		}
	}

	/**
	 * @param UUID[] $uuids
	 * @param string[] $timestamps
	 */
	protected function updateWorkflows( array $uuids, array $timestamps ) {
		/** @var Workflow[] $workflows */
		$workflows = $this->storage->getMulti( $uuids );
		foreach ( $workflows as $workflow ) {
			$timestamp = $timestamps[$workflow->getId()->getAlphadecimal()];
			$workflow->updateLastModified( UUID::getComparisonUUID( $timestamp ) );
		}

		$this->storage->multiPut( $workflows );

		$this->output( 'Fixed workflows: ' . implode( ', ', array_keys( $uuids ) ) . "\n" );
	}
}

$maintClass = 'FlowFixWorkflowLastUpdateTimestamp';
require_once RUN_MAINTENANCE_IF_MAIN;
