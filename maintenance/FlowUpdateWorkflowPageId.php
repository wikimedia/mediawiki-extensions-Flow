<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

use Flow\Container;
use Flow\Model\UUID;
use Flow\OccupationController;
use Flow\Maintenance\WorkflowPageIdUpdateGenerator;

/**
 * In some cases we have created workflow instances before the related Title
 * has an ArticleID assigned to it.  This goes through and sets that value
 *
 * @ingroup Maintenance
 */
class FlowUpdateWorkflowPageId extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update workflow_page_id with the page id of its specified ns/title";
		$this->setBatchSize( 300 );
	}

	/**
	 * Assembles the update components, runs them, and reports
	 * on what they did
	 */
	public function doDbUpdates() {
		global $wgFlowCluster, $wgLang;

		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );

		$it = new BatchRowIterator(
			$dbw,
			'flow_workflow',
			'workflow_id',
			$this->mBatchSize
		);
		$it->setFetchColumns( array( '*' ) );
		$it->addConditions( array(
			'workflow_wiki' => wfWikiId(),
		) );

		$gen = new WorkflowPageIdUpdateGenerator( $wgLang );
		$writer = new BatchRowWriter( $dbw, 'flow_workflow', $wgFlowCluster );
		$updater = new BatchRowUpdate( $it, $writer, $gen );

		$updater->execute();

		$this->output( $gen->report() );

		return true;
	}

	protected function getUpdateKey() {
		return __CLASS__;
	}
}

$maintClass = "FlowUpdateWorkflowPageId";
require_once( DO_MAINTENANCE );
