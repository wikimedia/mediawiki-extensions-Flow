<?php

use Flow\Container;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../../..';
}
require_once( "$IP/maintenance/Maintenance.php" );

/**
 * Update flow_revision.rev_type_id
 *
 * @ingroup Maintenance
 */
class FlowUpdateRevisionTypeId extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update flow_revision.rev_type_id";
		$this->setBatchSize( 300 );
	}

	protected function doDBUpdates() {
		global $wgFlowCluster;

		$revId = '';
		$count = $this->mBatchSize;
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );

		// If table flow_header_revision does not exist, that means the wiki
		// has run the data migration before or the wiki starts from scratch,
		// there is no point to run the script againt invalid tables
		if ( $dbr->tableExists( 'flow_header_revision', __METHOD__ ) ) {
			return true;
		}

		while ( $count == $this->mBatchSize ) {
			$count = 0;
			$res = $dbr->select(
				array( 'flow_revision', 'flow_tree_revision', 'flow_header_revision' ),
				array( 'rev_id', 'rev_type', 'tree_rev_descendant_id', 'header_workflow_id' ),
				array( 'rev_id > ' . $dbr->addQuotes( $revId ) ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_id ASC', 'LIMIT' => $this->mBatchSize ),
				array(
					'flow_tree_revision' => array( 'LEFT JOIN', 'rev_id=tree_rev_id' ),
					'flow_header_revision' => array( 'LEFT JOIN', 'rev_id=header_rev_id' )
				)
			);

			if ( $res ) {
				foreach ( $res as $row ) {
					$count++;
					$revId = $row->rev_id;
					switch ( $row->rev_type ) {
						case 'header':
							$this->updateRevision( $row->rev_id, $row->header_workflow_id );
						break;
						case 'post':
							$this->updateRevision( $row->rev_id, $row->tree_rev_descendant_id );
						break;
					}
				}
			} else {
				throw new MWException( 'SQL error in maintenance script ' . __CLASS__ . '::' . __METHOD__ );
			}
			wfWaitForSlaves( false, false, $wgFlowCluster );
		}

		return true;
	}

	private function updateRevision( $revId, $revTypeId ) {
		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );
		$res = $dbw->update(
			'flow_revision',
			array( 'rev_type_id' => $revTypeId ),
			array( 'rev_id' => $revId ),
			__METHOD__
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
		return 'FlowUpdateRevisionTypeId';
	}
}

$maintClass = 'FlowUpdateRevisionTypeId';
require_once( DO_MAINTENANCE );
