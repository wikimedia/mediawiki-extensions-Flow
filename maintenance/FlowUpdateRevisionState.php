<?php

use Flow\Container;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once( "$IP/maintenance/Maintenance.php" );

/**
 * Update flow_revision_state
 *
 * @ingroup Maintenance
 */
class FlowUpdateRevisionState extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update flow_revision_state";
		$this->setBatchSize( 300 );
	}

	protected function doDBUpdates() {
		$revId = '';
		$count = $this->mBatchSize;
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_SLAVE );
		$dbw = $dbFactory->getDB( DB_MASTER );

		while ( $count == $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_revision' ),
				array(
					'frs_rev_id' => 'rev_id',
					'frs_state' => 'rev_mod_state',
					'frs_user_id' => 'rev_mod_user_id',
					'frs_user_ip' => 'rev_mod_user_ip',
					'frs_user_wiki' => 'rev_mod_user_wiki',
					'frs_comment' => 'rev_mod_reason'
				),
				array( 'rev_id > ' . $dbr->addQuotes( $revId ) ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_id ASC', 'LIMIT' => $this->mBatchSize )
			);

			if ( $res ) {
				foreach ( $res as $row ) {
					$count++;
					$revId = $row->frs_rev_id;
					$this->insertRevisionState( $dbw, $row );
				}
			} else {
				throw new MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
			$dbFactory->waitForSlaves();
		}

		return true;
	}

	private function insertRevisionState( $dbw, $row ) {
		$res = $dbw->insert(
			'flow_revision_state',
			$insert,
			__METHOD__,
			array( 'IGNORE' )
		);
		if ( !$res ) {
			throw new MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
		}
	}

	/**
	 * Get the update key name to go in the update log table
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'FlowUpdateRevisionState';
	}
}

$maintClass = 'FlowUpdateRevisionState';
require_once( DO_MAINTENANCE );
