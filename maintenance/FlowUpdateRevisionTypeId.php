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
 * Update flow_revision.rev_type_id
 *
 * @ingroup Maintenance
 */
class FlowUpdateRevisionTypeId extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Update flow_revision.rev_type_id" );
		$this->requireExtension( 'Flow' );
		$this->setBatchSize( 300 );
	}

	protected function doDBUpdates() {
		$revId = '';
		$batchSize = $this->getBatchSize();
		$count = $this->getBatchSize();
		/** @var DbFactory $dbFactory */
		$dbFactory = Container::get( 'db.factory' );
		$dbr = $dbFactory->getDB( DB_REPLICA );
		$dbw = $dbFactory->getDB( DB_PRIMARY );

		// If table flow_header_revision does not exist, that means the wiki
		// has run the data migration before or the wiki starts from scratch,
		// there is no point to run the script against invalid tables
		if ( !$dbr->tableExists( 'flow_header_revision', __METHOD__ ) ) {
			return true;
		}

		while ( $count == $batchSize ) {
			$count = 0;
			$res = $dbr->newSelectQueryBuilder()
				->select( [ 'rev_id', 'rev_type', 'tree_rev_descendant_id', 'header_workflow_id' ] )
				->from( 'flow_revision' )
				->leftJoin( 'flow_tree_revision', null, 'rev_id=tree_rev_id' )
				->leftJoin( 'flow_header_revision', null, 'rev_id=header_rev_id' )
				->where( $dbr->expr( 'rev_id', '>', $revId ) )
				->orderBy( 'rev_id' )
				->limit( $batchSize )
				->caller( __METHOD__ )
				->fetchResultSet();

			foreach ( $res as $row ) {
				$count++;
				$revId = $row->rev_id;
				switch ( $row->rev_type ) {
					case 'header':
						$this->updateRevision( $dbw, $row->rev_id, $row->header_workflow_id );
						break;
					case 'post':
						$this->updateRevision( $dbw, $row->rev_id, $row->tree_rev_descendant_id );
						break;
				}
			}
			$dbFactory->waitForReplicas();
		}

		$dbw->dropTable( 'flow_header_revision', __METHOD__ );

		return true;
	}

	/**
	 * @param IDatabase $dbw
	 * @param int $revId
	 * @param string $revTypeId
	 */
	private function updateRevision( $dbw, $revId, $revTypeId ) {
		if ( $revTypeId === null ) {
			// this shouldn't actually be happening, but if it is, ignoring it
			// will not make things worse - the revision is lost already
			return;
		}

		$dbw->newUpdateQueryBuilder()
			->update( 'flow_revision' )
			->set( [ 'rev_type_id' => $revTypeId ] )
			->where( [ 'rev_id' => $revId ] )
			->caller( __METHOD__ )
			->execute();
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

$maintClass = FlowUpdateRevisionTypeId::class;
require_once RUN_MAINTENANCE_IF_MAIN;
