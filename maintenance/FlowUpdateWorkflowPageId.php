<?php

use Flow\Container;
use Flow\Model\UUID;
use Flow\Model\PostRevision;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../../..';
}
require_once( "$IP/maintenance/Maintenance.php" );
require_once __DIR__ . "/../../Echo/includes/BatchRowUpdate.php";

/**
 * In some cases we have created workflow instances before the related Title
 * has an ArticleID assigned to it.  This goes through and sets that value
 *
 * @ingroup Maintenance
 */
class FlowUpdateWorkflowPageId extends LoggedUpdateMaintenance {

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update workflow_page_id with the page id of its specified ns/titlz";
		$this->setBatchSize( 300 );
	}

	/**
	 * Assembles the update components, runs them, and reports
	 * on what they did
	 */
	protected function doDBUpdates() {
		global $wgFlowCluster, $wgLang;

		$dbw = Flow\Container::get( 'db.factory' )->getDB( DB_MASTER );

		$it = new EchoBatchRowIterator(
			$dbw,
			'flow_workflow',
			'workflow_id',
			$this->mBatchSize
		);
		$it->setFetchColumns( array( '*' ) );
		$it->addConditions( array(
			'workflow_wiki' => wfWikiId(),
			'workflow_page_id' => 0,
		) );


		$gen = new WorkflowPageIdUpdateGenerator( $wgLang );
		$writer = new EchoBatchRowWriter( $dbw, 'flow_workflow', $wgFlowCluster );
		$updater = new EchoBatchRowUpdate( $it, $writer, $gen );

		$updater->execute();

		$this->output( $gen->report() );

		return true;
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

/**
 * Looks at rows from the flow_workflow table and returns an update
 * for the workflow_page_id field if necessary.
 */
class WorkflowPageIdUpdateGenerator implements EchoRowUpdateGenerator {
	/**
	 * @var Language|StubUserLang
	 */
	protected $lang;
	protected $fixedCount = 0;
	protected $failed = array();

	/**
	 * @param Language|StubUserLang $lang
	 */
	public function __construct( $lang ) {
		$this->lang = $lang;
	}

	public function update( $row ) {
		$title = Title::makeTitleSafe( $row->workflow_namespace, $row->workflow_title_text );
		if ( $title === null ) {
			throw new Exception( sprintf(
				'Could not create title for %s at %s:%s',
				UUID::create( $row->workflow_id )->getAlphadecimal(),
				$this->lang->getNsText( $row->workflow_namespace ) ?: $row->workflow_namespace,
				$row->workflow_title_text
			) );
		}

		if ( $title->getArticleID() !== (int)$row->workflow_page_id ) {
			// This makes the assumption the page has not moved or been deleted?
			++$this->fixedCount;
			return array(
				'workflow_page_id' => $title->getArticleID(),
			);
		} elseif ( !$row->workflow_page_id ) {
			// No id exists for this workflow?
			$this->failed[] = $row;
		}

		return array();
	}

	public function report() {
		return "Updated {$this->fixedCount}  workflows\nFailed: " . count( $this->failed ) . "\n\n" . print_r( $this->failed, true );
	}
}

$maintClass = "FlowUpdateWorkflowPageId";
require_once( DO_MAINTENANCE );
