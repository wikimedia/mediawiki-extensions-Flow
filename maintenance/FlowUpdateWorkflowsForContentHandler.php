<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Moves all topic workflows to NS_TOPIC
 *
 * @ingroup Maintenance
 */
class FlowUpdateWorkflowsForContentHandler extends LoggedUpdateMaintenance {
	/**
	 * The number of entries completed
	 *
	 * @var int
	 */
	private $completeCount = 0;

	/**
	 * @var Flow\Data\ObjectManager
	 */
	private $storage;

	protected function doDBUpdates() {
		$dbf = Container::get( 'db.factory' );
		$dbw = $dbf->getDB( DB_MASTER );
		$this->storage = Container::get( 'storage.workflow' );

		$continue = "\0";
		$step = 0;
		do {
			$continue = $this->updateWorkflows( $dbw, $continue );
			$dbf->waitForSlaves();
			echo '.', ( ++$step % 80 === 0 ) ? "\n" : '';
		} while ( $continue !== null );

		echo "\nUpdated {$this->completeCount} topics\n";
		return true;
	}

	/**
	 * Refreshes a batch of recentchanges entries
	 *
	 * @param DatabaseBase $dbw
	 * @param int[optional] $continue The next batch starting at rc_id
	 * @return int Start id for the next batch
	 */
	public function updateWorkflows( DatabaseBase $dbw, $continue = "\0" ) {
		$rows = $dbw->select(
			/* table */'flow_workflow',
			/* select */array( '*' ),
			/* conds */array(
				'workflow_id > ' . $dbw->addQuotes( $continue ),
				'workflow_type' => 'topic',
			),
			__METHOD__,
			/* options */array( 'LIMIT' => $this->mBatchSize, 'ORDER BY' => 'workflow_id' )
		);

		$continue = null;

		// moving a workflow should not be a normal procedure, so not supporting
		// it directly in the object and moving via reflection instead
		// @todo this means we no longer know which page initially
		// created the topic, do we care?
		$reflNs = new ReflectionProperty( 'Flow\\Model\\Workflow', 'namespace' );
		$reflNs->setAccessible( true );
		$reflTitle = new ReflectionProperty( 'Flow\\Model\\Workflow', 'titleText' );
		$reflTitle->setAccessible( true );

		foreach ( $rows as $row ) {
			$continue = $row->workflow_id;

			$title = Title::newFromText(
				UUID::create( $row->workflow_id )->getAlphadecimal(),
				NS_TOPIC
			);

			// Rather than directly updating $dbw we load the topic through the storage
			// layer so the updates hit the caches as well
			$workflow = $this->storage->get( array( 'workflow_id' => $row->workflow_id ) );
			if ( !$workflow ) {
				throw new FlowException( '...' );
			}

			$reflNs->setValue( $workflow, $title->getNamespace() );
			$reflTitle->setValue( $workflow, $title->getDBkey() );

			$this->storage->put( $workflow );
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
		return __CLASS__;
	}
}

$maintClass = 'FlowUpdateWorkflowsForContentHandler'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );
