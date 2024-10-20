<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\UUID;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\User\User;
use MediaWiki\WikiMap\WikiMap;
use Wikimedia\Rdbms\IReadableDatabase;

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
class FlowFixEditCount extends LoggedUpdateMaintenance {
	/**
	 * Array of [username => increased edit count]
	 *
	 * @var array
	 */
	protected $updates = [];

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Adjusts edit counts for all existing Flow data' );

		$this->addOption( 'start', 'Timestamp to start counting revisions at', false, true );
		$this->addOption( 'stop', 'Timestamp to stop counting revisions at', false, true );

		$this->setBatchSize( 300 );

		$this->requireExtension( 'Flow' );
	}

	protected function getUpdateKey() {
		return 'FlowFixEditCount';
	}

	protected function doDBUpdates() {
		/** @var IReadableDatabase $dbr */
		$dbr = Container::get( 'db.factory' )->getDB( DB_REPLICA );
		$countableActions = $this->getCountableActions();

		// defaults = date of first Flow commit up until now
		$continue = UUID::getComparisonUUID( $this->getOption( 'start', '20130710230511' ) );
		$stop = UUID::getComparisonUUID( $this->getOption( 'stop', time() ) );

		while ( $continue !== false ) {
			$continue = $this->refreshBatch( $dbr, $continue, $countableActions, $stop );

			// wait for core (we're updating user table) replicas to catch up
			$this->waitForReplication();
		}

		$this->output( "Done increasing edit counts. Increased:\n" );
		foreach ( $this->updates as $userId => $count ) {
			$userName = User::newFromId( $userId )->getName();
			$this->output( "  User $userId ($userName): +$count\n" );
		}

		return true;
	}

	public function refreshBatch( IReadableDatabase $dbr, UUID $continue, array $countableActions, UUID $stop ) {
		$rows = $dbr->newSelectQueryBuilder()
			->select( [ 'rev_id', 'rev_user_id' ] )
			->from( 'flow_revision' )
			->where( [
				$dbr->expr( 'rev_id', '>', $continue->getBinary() ),
				$dbr->expr( 'rev_id', '<=', $stop->getBinary() ),
				$dbr->expr( 'rev_user_id', '>', 0 ),
				'rev_user_wiki' => WikiMap::getCurrentWikiId(),
				'rev_change_type' => $countableActions,
			] )
			->orderBy( 'rev_id' )
			->limit( $this->getBatchSize() )
			->caller( __METHOD__ )
			->fetchResultSet();

		// end of data
		if ( $rows->numRows() === 0 ) {
			return false;
		}

		$userEditTracker = $this->getServiceContainer()->getUserEditTracker();
		foreach ( $rows as $row ) {
			// UserEditTracker::incrementUserEditCount only allows for edit count to be
			// increased 1 at a time. It'd be better to immediately be able to increase
			// the edit count by the exact number it should be increased with, but
			// I'd rather re-use existing code, especially in a run-once script,
			// where performance is not the most important thing ;)
			$user = User::newFromId( $row->rev_user_id );
			$userEditTracker->incrementUserEditCount( $user );

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
	 * @return string[]
	 */
	protected function getCountableActions() {
		$allowedActions = [];

		/** @var FlowActions $actions */
		$actions = Container::get( 'flow_actions' );
		foreach ( $actions->getActions() as $action ) {
			if ( $actions->getValue( $action, 'editcount' ) ) {
				$allowedActions[] = $action;
			}
		}

		return $allowedActions;
	}
}

$maintClass = FlowFixEditCount::class;
require_once RUN_MAINTENANCE_IF_MAIN;
