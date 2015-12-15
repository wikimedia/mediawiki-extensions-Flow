<?php

use Flow\Container;
use Flow\Maintenance\UpdateWorkflowLastUpdateTimestampGenerator;
use Flow\Maintenance\UpdateWorkflowLastUpdateTimestampWriter;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * @ingroup Maintenance
 */
class FlowFixWorkflowLastUpdateTimestamp extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Fixes any incorrect workflow_last_update_timestamp for topics';

		$this->setBatchSize( 10 );
	}

	public function execute() {
		global $wgFlowCluster;

		$dbFactory = Container::get( 'db.factory' );
		$storage = Container::get( 'storage' );
		$rootPostLoader = Container::get( 'loader.root_post' );

		$iterator = new BatchRowIterator( $dbFactory->getDB( DB_SLAVE ), 'flow_workflow', 'workflow_id', $this->mBatchSize );
		$iterator->setFetchColumns( array( 'workflow_id', 'workflow_type', 'workflow_last_update_timestamp' ) );
		$iterator->addConditions( array( 'workflow_wiki' => wfWikiId() ) );

		$updater = new BatchRowUpdate(
			$iterator,
			new UpdateWorkflowLastUpdateTimestampWriter( $storage, $wgFlowCluster ),
			new UpdateWorkflowLastUpdateTimestampGenerator( $storage, $rootPostLoader )
		);
		$updater->setOutput( array( $this, 'output' ) );
		$updater->execute();
	}

	/**
	 * parent::output() is a protected method, only way to access it from a
	 * callback in php5.3 is to make a public function. In 5.4 can replace with
	 * a Closure.
	 *
	 * @param string $out
	 * @param mixed $channel
	 */
	public function output( $out, $channel = null ) {
		parent::output( $out, $channel );
	}
}

$maintClass = 'FlowFixWorkflowLastUpdateTimestamp';
require_once RUN_MAINTENANCE_IF_MAIN;
