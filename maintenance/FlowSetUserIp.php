<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\DbFactory;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use Wikimedia\Rdbms\IDatabase;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Populate the *_user_ip fields within flow. This only updates
 * the database and not the cache. The model loading layer handles
 * cached old values.
 *
 * @ingroup Maintenance
 */
class FlowSetUserIp extends LoggedUpdateMaintenance {
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
		/** @var DbFactory $dbf */
		$dbf = Container::get( 'db.factory' );
		$dbw = $dbf->getDB( DB_PRIMARY );
		$hasRun = false;

		$runUpdate = static function ( $callback ) use ( $dbf, $dbw, &$hasRun ) {
			$hasRun = true;
			$continue = "\0";
			do {
				$continue = $callback( $dbw, $continue );
				$dbf->waitForReplicas();
			} while ( $continue !== null );
		};

		// run updates only if we have the required source data
		if ( $dbw->fieldExists( 'flow_workflow', 'workflow_user_text' ) ) {
			$runUpdate( [ $this, 'updateWorkflow' ] );
		}
		if ( $dbw->fieldExists( 'flow_tree_revision', 'tree_orig_user_text' ) ) {
			$runUpdate( [ $this, 'updateTreeRevision' ] );
		}
		if (
			$dbw->fieldExists( 'flow_revision', 'rev_user_text' ) &&
			$dbw->fieldExists( 'flow_revision', 'rev_mod_user_text' ) &&
			$dbw->fieldExists( 'flow_revision', 'rev_edit_user_text' )
		) {
			$runUpdate( [ $this, 'updateRevision' ] );
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
	public function updateWorkflow( IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'workflow_id', 'workflow_user_text' ] )
			->from( 'flow_workflow' )
			->where( [
				$dbw->expr( 'workflow_id', '>', $continue ),
				'workflow_user_ip' => null,
				'workflow_user_id' => 0,
			] )
			->limit( $this->getBatchSize() )
			->orderBy( 'workflow_id' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$continue = null;

		foreach ( $rows as $row ) {
			$continue = $row->workflow_id;
			$dbw->newUpdateQueryBuilder()
				->update( 'flow_workflow' )
				->set( [ 'workflow_user_ip' => $row->workflow_user_text ] )
				->where( [ 'workflow_id' => $row->workflow_id ] )
				->caller( __METHOD__ )
				->execute();

			$this->completeCount++;
		}

		return $continue;
	}

	public function updateTreeRevision( IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'tree_rev_id', 'tree_orig_user_text' ] )
			->from( 'flow_tree_revision' )
			->where( [
				$dbw->expr( 'tree_rev_id', '>', $continue ),
				'tree_orig_user_ip' => null,
				'tree_orig_user_id' => 0,
			] )
			->limit( $this->getBatchSize() )
			->orderBy( 'tree_rev_id' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$continue = null;
		foreach ( $rows as $row ) {
			$continue = $row->tree_rev_id;
			$dbw->newUpdateQueryBuilder()
				->update( 'flow_tree_revision' )
				->set( [ 'tree_orig_user_ip' => $row->tree_orig_user_text ] )
				->where( [ 'tree_rev_id' => $row->tree_rev_id ] )
				->caller( __METHOD__ )
				->execute();

			$this->completeCount++;
		}

		return $continue;
	}

	public function updateRevision( IDatabase $dbw, $continue = null ) {
		$rows = $dbw->newSelectQueryBuilder()
			->select( [ 'rev_id', 'rev_user_id', 'rev_user_text', 'rev_mod_user_id',
				'rev_mod_user_text', 'rev_edit_user_id', 'rev_edit_user_text' ] )
			->from( 'flow_revision' )
			->where( [
				$dbw->expr( 'rev_id', '>', $continue ),
				$dbw->expr( 'rev_user_id', '=', 0 )
					->or( 'rev_mod_user_id', '=', 0 )
					->or( 'rev_edit_user_id', '=', 0 ),
			] )
			->limit( $this->getBatchSize() )
			->orderBy( 'rev_id' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$continue = null;
		foreach ( $rows as $row ) {
			$continue = $row->rev_id;
			$updates = [];

			if ( $row->rev_user_id == 0 ) {
				$updates['rev_user_ip'] = $row->rev_user_text;
			}
			if ( $row->rev_mod_user_id == 0 ) {
				$updates['rev_mod_user_ip'] = $row->rev_mod_user_text;
			}
			if ( $row->rev_edit_user_id == 0 ) {
				$updates['rev_edit_user_ip'] = $row->rev_edit_user_text;
			}
			if ( $updates ) {
				$dbw->newUpdateQueryBuilder()
					->update( 'flow_revision' )
					->set( $updates )
					->where( [ 'rev_id' => $row->rev_id ] )
					->caller( __METHOD__ )
					->execute();
			}
		}

		return $continue;
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowSetUserIp';
	}
}

$maintClass = FlowSetUserIp::class;
require_once RUN_MAINTENANCE_IF_MAIN;
