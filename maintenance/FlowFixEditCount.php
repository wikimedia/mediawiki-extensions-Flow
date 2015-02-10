<?php

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Adjusts edit counts for all existing Flow data.
 *
 * @ingroup Maintenance
 */
class FlowFixEditCount extends LoggedUpdateMaintenance {
	/**
	 * Array of [username => increased edit count]
	 *
	 * @var array
	 */
	protected $updates = array();

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Adjusts edit counts for all existing Flow data';

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return 'FlowFixEditCount';
	}

	protected function doDBUpdates() {
		/** @var DatabaseBase $dbr */
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
		$countableActions = $this->getCountableActions();

		// date of first Flow commit - we can safely start processing batch
		// here, as there's no possibility of older data
		$continue = UUID::getComparisonUUID( '20130710230511' );
		while ( $continue !== false ) {
			$continue = $this->refreshBatch( $dbr, $continue, $countableActions );

			// wait for core (we're updating user table) slaves to catch up
			wfWaitForSlaves();
		}

		$this->output( "Done increasing edit counts. Increased:\n" );
		foreach ( $this->updates as $userId => $count ) {
			$userName = User::newFromId( $userId )->getName();
			$this->output( "  User $userId ($userName): +$count\n" );
		}

		return true;
	}

	public function refreshBatch( DatabaseBase $dbr, UUID $continue, $countableActions ) {
		$rows = $dbr->select(
			'flow_revision',
			array( 'rev_id', 'rev_user_id' ),
			array(
				'rev_id > ' . $dbr->addQuotes( $continue->getBinary() ),
				'rev_user_id > 0',
				'rev_change_type' => $countableActions,
			),
			__METHOD__,
			array( 'ORDER BY' => 'rev_id ASC' )
		);

		// end of data
		if ( !$rows || $rows->numRows() === 0 ) {
			return false;
		}

		foreach ( $rows as $row ) {
			// User::incEditCount only allows for edit count to be increased 1
			// at a time. It'd be better to immediately be able to increase the
			// edit count by the exact number it should be increased with, but
			// I'd rather re-use existing code, especially in a run-once script,
			// where performance is not the most important thing ;)
			$user = User::newFromId( $row->rev_user_id );
			$user->incEditCount();

			// save updates so we can print them when the script is done running
			if ( !isset( $this->updates[$user->getId()] ) ) {
				$this->updates[$user->getId()] = 0;
			}
			$this->updates[$user->getId()]++;

			// set value for next batch to continue at
			$continue = $row->rev_id;
		}

		return UUID::create( $continue );
	}

	/**
	 * Returns list of rev_change_type values that warrant an editcount increase.
	 *
	 * @return array
	 */
	protected function getCountableActions() {
		$allowedActions = array();

		/** @var FlowActions $actions */
		$actions = \Flow\Container::get( 'flow_actions' );
		foreach ( $actions->getActions() as $action ) {
			if ( $actions->getValue( $action, 'editcount' ) ) {
				$allowedActions[] = $action;
			}
		}

		return $allowedActions;
	}
}

$maintClass = 'FlowFixEditCount';
require_once( RUN_MAINTENANCE_IF_MAIN );
