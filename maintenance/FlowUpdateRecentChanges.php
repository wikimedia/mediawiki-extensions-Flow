<?php

namespace Flow\Maintenance;

use Flow\Data\Listener\RecentChangesListener;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use Wikimedia\AtEase\AtEase;
use Wikimedia\Rdbms\IDatabase;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Updates recentchanges entries to contain information to build the
 * AbstractBlock objects.
 *
 * @ingroup Maintenance
 */
class FlowUpdateRecentChanges extends LoggedUpdateMaintenance {
	/**
	 * The number of entries completed
	 *
	 * @var int
	 */
	private $completeCount = 0;

	public function __construct() {
		parent::__construct();

		$this->setBatchSize( 300 );
		$this->requireExtension( 'Flow' );
	}

	protected function doDBUpdates() {
		$dbw = $this->getPrimaryDB();

		$continue = 0;

		while ( $continue !== null ) {
			$continue = $this->refreshBatch( $dbw, $continue );
			$this->waitForReplication();
		}

		return true;
	}

	/**
	 * Refreshes a batch of recentchanges entries
	 *
	 * @param IDatabase $dbw
	 * @param int|null $continue The next batch starting at rc_id
	 * @return int|null Start id for the next batch
	 */
	public function refreshBatch( IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'rc_id', 'rc_params' ] )
			->from( 'recentchanges' )
			->where( [
				$dbw->expr( 'rc_id', '>', $continue ),
				'rc_source' => RecentChangesListener::SRC_FLOW
			] )
			->limit( $this->getBatchSize() )
			->orderBy( 'rc_id' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$continue = null;

		foreach ( $rows as $row ) {
			$continue = $row->rc_id;

			// build params
			AtEase::suppressWarnings();
			$params = unserialize( $row->rc_params );
			AtEase::restoreWarnings();
			if ( !$params ) {
				$params = [];
			}

			// Don't fix entries that have been dealt with already
			if ( !isset( $params['flow-workflow-change']['type'] ) ) {
				continue;
			}

			// Set action, based on older 'type' values
			switch ( $params['flow-workflow-change']['type'] ) {
				case 'flow-rev-message-edit-title':
				case 'flow-edit-title':
					$params['flow-workflow-change']['action'] = 'edit-title';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-new-post':
				case 'flow-new-post':
					$params['flow-workflow-change']['action'] = 'new-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-edit-post':
				case 'flow-edit-post':
					$params['flow-workflow-change']['action'] = 'edit-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-reply':
				case 'flow-reply':
					$params['flow-workflow-change']['action'] = 'reply';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-restored-post':
				case 'flow-post-restored':
					$params['flow-workflow-change']['action'] = 'restore-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-hid-post':
				case 'flow-post-hidden':
					$params['flow-workflow-change']['action'] = 'hide-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-deleted-post':
				case 'flow-post-deleted':
					$params['flow-workflow-change']['action'] = 'delete-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-censored-post':
				case 'flow-post-censored':
					$params['flow-workflow-change']['action'] = 'suppress-post';
					$params['flow-workflow-change']['block'] = 'topic';
					$params['flow-workflow-change']['revision_type'] = 'PostRevision';
					break;

				case 'flow-rev-message-edit-header':
				case 'flow-edit-summary':
					$params['flow-workflow-change']['action'] = 'edit-header';
					$params['flow-workflow-change']['block'] = 'header';
					$params['flow-workflow-change']['revision_type'] = 'Header';
					break;

				case 'flow-rev-message-create-header':
				case 'flow-create-summary':
				case 'flow-create-header':
					$params['flow-workflow-change']['action'] = 'create-header';
					$params['flow-workflow-change']['block'] = 'header';
					$params['flow-workflow-change']['revision_type'] = 'Header';
					break;
			}

			unset( $params['flow-workflow-change']['type'] );

			// update log entry
			$dbw->newUpdateQueryBuilder()
				->update( 'recentchanges' )
				->set( [ 'rc_params' => serialize( $params ) ] )
				->where( [ 'rc_id' => $row->rc_id ] )
				->caller( __METHOD__ )
				->execute();

			$this->completeCount++;
		}

		return $continue;
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowUpdateRecentChanges';
	}
}

$maintClass = FlowUpdateRecentChanges::class;
require_once RUN_MAINTENANCE_IF_MAIN;
