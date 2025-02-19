<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use BatchRowUpdate;
use BatchRowWriter;
use Exception;
use Flow\Container;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use MediaWiki\Language\Language;
use MediaWiki\Language\RawMessage;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\StubObject\StubUserLang;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;
use RowUpdateGenerator;
use RuntimeException;
use stdClass;
use Wikimedia\Rdbms\IDBAccessObject;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * In some cases we have created workflow instances before the related Title
 * has an ArticleID assigned to it.  This goes through and sets that value
 *
 * @ingroup Maintenance
 */
class FlowUpdateWorkflowPageId extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Update workflow_page_id with the page id of its specified ns/title" );
		$this->requireExtension( 'Flow' );
		$this->setBatchSize( 300 );
	}

	/**
	 * Assembles the update components, runs them, and reports
	 * on what they did
	 * @return true
	 */
	public function doDbUpdates() {
		global $wgFlowCluster, $wgLang;

		$dbw = Container::get( 'db.factory' )->getDB( DB_PRIMARY );

		$it = new BatchRowIterator(
			$dbw,
			'flow_workflow',
			'workflow_id',
			$this->getBatchSize()
		);
		$it->setFetchColumns( [ '*' ] );
		$it->addConditions( [
			'workflow_wiki' => WikiMap::getCurrentWikiId(),
		] );
		$it->setCaller( __METHOD__ );

		$gen = new WorkflowPageIdUpdateGenerator( $wgLang );
		$writer = new BatchRowWriter( $dbw, 'flow_workflow', $wgFlowCluster );
		$writer->setCaller( __METHOD__ );
		$updater = new BatchRowUpdate( $it, $writer, $gen );

		$updater->execute();

		$this->output( $gen->report() );

		return true;
	}

	protected function getUpdateKey() {
		return 'FlowUpdateWorkflowPageId';
	}
}

/**
 * Looks at rows from the flow_workflow table and returns an update
 * for the workflow_page_id field if necessary.
 */
class WorkflowPageIdUpdateGenerator implements RowUpdateGenerator {
	/**
	 * @var Language|StubUserLang
	 */
	protected $lang;
	/** @var int */
	protected $fixedCount = 0;
	/** @var stdClass[] */
	protected $failures = [];
	/** @var string[] */
	protected $warnings = [];

	/**
	 * @param Language|StubUserLang $lang
	 */
	public function __construct( $lang ) {
		$this->lang = $lang;
	}

	public function update( $row ) {
		$title = Title::makeTitleSafe( $row->workflow_namespace, $row->workflow_title_text );
		if ( $title === null ) {
			throw new RuntimeException( sprintf(
				'Could not create title for %s at %s:%s',
				UUID::create( $row->workflow_id )->getAlphadecimal(),
				$this->lang->getNsText( $row->workflow_namespace ) ?: $row->workflow_namespace,
				$row->workflow_title_text
			) );
		}

		// at some point, we failed to create page entries for new workflows: only
		// create that page if the workflow was stored with a 0 page id (otherwise,
		// we could mistake the $title for a deleted page)
		if ( (int)$row->workflow_page_id === 0 && $title->getArticleID() === 0 ) {
			$workflow = Workflow::fromStorageRow( (array)$row );
			$status = $this->createPage( $title, $workflow );
			if ( !$status->isGood() ) {
				// just warn when we failed to create the page, but keep this code
				// going and see if we manage to associate the workflow anyways
				// (or if that fails, we'll also get an error there)
				$this->warnings[] = $status->getMessage()->text();
			}
		}

		// re-associate the workflow with the correct page; only if a page exists
		if ( $title->getArticleID() !== 0 && $title->getArticleID() !== (int)$row->workflow_page_id ) {
			// This makes the assumption the page has not moved or been deleted?
			++$this->fixedCount;
			return [
				'workflow_page_id' => $title->getArticleID(),
			];
		} elseif ( !$row->workflow_page_id ) {
			// No id exists for this workflow? (reason should likely show up in $this->warnings)
			$this->failures[] = $row;
		}

		return [];
	}

	/**
	 * @param Title $title
	 * @param Workflow $workflow
	 * @return Status
	 */
	protected function createPage( Title $title, $workflow ) {
		/** @var OccupationController $occupationController */
		$occupationController = Container::get( 'occupation_controller' );

		try {
			$status = $occupationController->safeAllowCreation( $title, $occupationController->getTalkpageManager() );
			$status2 = $occupationController->ensureFlowRevision(
				MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title ),
				$workflow
			);

			$status->merge( $status2 );
		} catch ( Exception $e ) {
			// "convert" exception into Status
			$message = new RawMessage( $e->getMessage() );
			$status = Status::newFatal( $message );
		}

		if ( $status->isGood() ) {
			// force article id to be refetched from db
			$title->getArticleID( IDBAccessObject::READ_LATEST );
		}

		return $status;
	}

	public function report() {
		$ret = "Updated {$this->fixedCount} workflows\n\n";

		$warningsCount = count( $this->warnings );
		$ret .= "Warnings: {$warningsCount}\n";
		if ( $warningsCount > 0 ) {
			$ret .= print_r( $this->warnings, true ) . "\n\n";
		}
		$failureCount = count( $this->failures );
		$ret .= "Failed: {$failureCount}\n";
		if ( $failureCount > 0 ) {
			$ret .= print_r( $this->failures, true );
		}

		return $ret;
	}
}

$maintClass = FlowUpdateWorkflowPageId::class;
require_once RUN_MAINTENANCE_IF_MAIN;
