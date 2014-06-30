<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Populate the *_user_ip fields within flow.  This only updates
 * the database and not the cache.  The model loading layer handles
 * cached old values.
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

	protected function doDBUpdates() {
		$dbf = Container::get( 'db.factory' );
		$dbw = $dbf->getDB( DB_MASTER );

		$continue = "\0";
		do {
			$continue = $this->updateWorkflows( $dbw, $continue );
			$dbf->waitForSlaves();
		} while ( $continue !== null );

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
			/* select */array( 'workflow_id' ),
			/* conds */array(
				'workflow_id > ' . $dbw->addQuotes( $continue ),
				'workflow_type' => 'topic',
			),
			__METHOD__,
			/* options */array( 'LIMIT' => $this->mBatchSize, 'ORDER BY' => 'workflow_id' )
		);

		$continue = null;

		foreach ( $rows as $row ) {
			$continue = $row->workflow_id;

			$title = Title::newFromText(
				UUID::create( $row->workflow_id )->getAlphadecimal(),
				NS_TOPIC
			);

			// @todo this means we no longer know which page initially
			// created the topic, do we care?
			$dbw->update(
				/* table */'flow_workflow',
				/* update */array(
					'workflow_namespace' => $title->getNamespace(),
					'workflow_title_text' => $title->getDBkey()
				),
				/* conditions */array( 'workflow_id' => $row->workflow_id ),
				__METHOD__
			);

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
