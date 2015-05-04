<?php

use Flow\Container;
use Flow\Model\UUID;
use Flow\OccupationController;

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
class FlowUpdateWorkflowPageId extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update workflow_page_id with the page id of its specified ns/title";
		$this->setBatchSize( 300 );
	}

	/**
	 * Assembles the update components, runs them, and reports
	 * on what they did
	 */
	public function execute() {
		global $wgFlowCluster, $wgLang;

		$dbw = Container::get( 'db.factory' )->getDB( DB_MASTER );

		$it = new EchoBatchRowIterator(
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
		$writer = new EchoBatchRowWriter( $dbw, 'flow_workflow', $wgFlowCluster );
		$updater = new EchoBatchRowUpdate( $it, $writer, $gen );

		$updater->execute();

		$this->output( $gen->report() );

		return true;
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

		// at some point, we failed to create page entries for new workflows: only
		// create that page if the workflow was stored with a 0 page id (otherwise,
		// we could mistake the $title for a deleted page)
		if ( $row->workflow_page_id === 0 && $title->getArticleID() === 0 ) {
			// build workflow object (yes, loading them piecemeal is suboptimal, but
			// this is just a one-time script; considering the alternative is
			// creating a derivative EchoBatchRowIterator that returns workflows,
			// it doesn't really matter)
			$storage = Container::get( 'storage' );
			$workflow = $storage->get( 'Workflow', UUID::create( $row->workflow_id ) );

			try {
				/** @var OccupationController $occupationController */
				$occupationController = Container::get( 'occupation_controller' );
				$occupationController->allowCreation( $title, $occupationController->getTalkpageManager() );
				$occupationController->ensureFlowRevision( new Article( $title ), $workflow );

				// force article id to be refetched from db
				$title->getArticleID( Title::GAID_FOR_UPDATE );
			} catch ( \Exception $e ) {
				// catch all exception to keep going with the rest we want to
				// iterate over, we'll report on the failed entries at the end
				$this->failed[] = $row;
			}
		}

		// re-associate the workflow with the correct page; only if a page exists
		if ( $title->getArticleID() !== 0 && $title->getArticleID() !== (int) $row->workflow_page_id ) {
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
