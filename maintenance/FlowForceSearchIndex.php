<?php

use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Search\Connection;
use Flow\Search\Updater;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's forceSearchIndex, this will force indexing of Flow
 * data in ElasticSearch.
 *
 * @ingroup Maintenance
 */
class FlowForceSearchIndex extends Maintenance {
	// @todo: do we need to steal more from Cirrus' ForceSearchIndex? What options are important?

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Force indexing Flow revisions (headers & topics).';

		$this->setBatchSize( 10 );

		$this->addOption( 'fromId', 'Start indexing at a specific revision id (inclusive).', false, true );
		$this->addOption( 'toId', 'Stop indexing at a specific revision (inclusive).', false, true );
		$this->addOption( 'limit', 'Maximum number of revisions to process before exiting the script. Default to unlimited.', false, true );
		$this->addOption( 'namespace', 'Only index revisions in this given namespace', false, true );
	}

	public function execute() {
		global $wgFlowSearchMaintenanceTimeout;

		// Set the timeout for maintenance actions
		Connection::getSingleton()->setTimeout2( $wgFlowSearchMaintenanceTimeout );

		// get query conditions & options based on given parameters
		$options = array( 'LIMIT' => $this->mBatchSize );

		/** @var Updater[] $updaters */
		$updaters = Container::get( 'searchindex.updaters' );
		foreach ( $updaters as $updaterType => $updater ) {
			$fromId = $this->getOption( 'fromId', null );
			$fromId = $fromId ? UUID::create( $fromId ) : null;
			$toId = $this->getOption( 'toId', null );
			$toId = $toId ? UUID::create( $toId ) : null;
			$namespace = $this->getOption( 'namespace', null );
			$limit = $this->getOption( 'limit', null );
			$total = 0;

			while ( true ) {
				if ( $limit ) {
					$options['LIMIT'] = min( $limit, $this->mBatchSize );
					$limit -= $this->mBatchSize;

					if ( $options['LIMIT'] <= 0 ) {
						break;
					}
				}

				$conditions = $updater->buildQueryConditions( $fromId, $toId, $namespace );
				$revisions = $updater->getRevisions( $conditions, $options );

				// stop if we're all out of revisions
				if ( !$revisions ) {
					break;
				}

				$total += $updater->updateRevisions( $revisions, null, null );
				$this->output( "Indexed $total $updaterType document(s)\n" );

				// prepare for next batch, starting at the next id
				$prevFromId = $fromId;
				$fromId = $this->getNextFromId( $updater, $revisions );

				// make sure we don't get stuck in an infinite loop
				if ( $fromId === $prevFromId ) {
					$this->error(
						'Got stuck in an infinite loop.
						workflow_last_update_timestamp is likely incorrect for
						some workflows.
						run maintenance/FlowFixWorkflowLastUpdateTimestamp.php
						to automatically fix those.', 1 );
				}
			}
		}
	}

	/**
	 * @param Updater $updater
	 * @param AbstractRevision[] $revisions
	 * @return UUID
	 */
	protected function getNextFromId( Updater $updater, array $revisions ) {
		/** @var AbstractRevision $last */
		$last = array_pop( $revisions );

		if ( $last instanceof \Flow\Model\Header ) {
			$timestamp = $last->getRevisionId()->getTimestampObj();
		} else {
			$timestamp = $last->getCollection()->getWorkflow()->getLastModifiedObj();
		}

		// $timestamp is the timestamp of the last revision we fetched. fromId
		// is inclusive, and we don't want to include what we already have here,
		// so we'll advance 1 more and call that the next fromId
		$timestamp = (int) $timestamp->getTimestamp( TS_UNIX );
		return UUID::getComparisonUUID( $timestamp + 1 );
	}
}

$maintClass = 'FlowForceSearchIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
