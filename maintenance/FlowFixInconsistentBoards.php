<?php

use Flow\Container;
use Flow\Utils\InconsistentBoardFixer;
use MediaWiki\MediaWikiServices;

require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: __DIR__ . '/../../../maintenance/Maintenance.php';

/**
 * Changes Flow boards and their topics to be associated with their current title, based on the JSON content
 * Fixes inconsistent bugs like T138310.
 *
 * There is a dry run available.
 *
 * @ingroup Maintenance
 */
class FlowFixInconsistentBoards extends Maintenance {
	/**
	 * @var Flow\DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var Flow\WorkflowLoaderFactory
	 */
	protected $workflowLoaderFactory;

	/**
	 * @var Flow\BoardMover
	 */
	protected $boardMover;

	/**
	 * @var Flow\Data\ManagerGroup
	 */
	protected $storage;

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Changes Flow boards and their topics to be associated with their ' .
			'current title, based on the JSON content.  Must be run separately for each affected wiki.';

		$this->addOption( 'dry-run', 'Only prints the board names, without changing anything.' );
		$this->addOption( 'namespaceName', 'Name of namespace to check, otherwise all', false, true );
		$this->addOption( 'limit', 'Limit of inconsistent pages to identify (and fix if not a dry ' .
			'run). Defaults to no limit', false, true );

		$this->setBatchSize( 300 );
	}

	public function execute() {
		global $wgLang;

		$dryRun = $this->hasOption( 'dry-run' );
		$dbFactory = Container::get( 'db.factory' );
		$boardFixer = new InconsistentBoardFixer(
			$dbFactory,
			Container::get( 'factory.loader.workflow' ),
			Container::get( 'board_mover' ),
			Container::get( 'storage' ),
			MediaWikiServices::getInstance()->getRevisionStore(),
			$dryRun
		);

		$limit = $this->getOption( 'limit' );

		$wikiDbw = $dbFactory->getWikiDB( DB_MASTER );

		$iterator = new BatchRowIterator( $wikiDbw, 'page', 'page_id', $this->mBatchSize );
		$iterator->setFetchColumns( [ 'page_namespace', 'page_title', 'page_latest' ] );
		$iterator->addConditions( [
			'page_content_model' => CONTENT_MODEL_FLOW_BOARD,
		] );

		if ( $this->hasOption( 'namespaceName' ) ) {
			$namespaceName = $this->getOption( 'namespaceName' );
			$namespaceId = $wgLang->getNsIndex( $namespaceName );

			if ( !$namespaceId ) {
				$this->error( "'$namespaceName' is not a valid namespace name" );
				return false;
			}

			if ( $namespaceId == NS_TOPIC ) {
				$this->error( 'This script can not be run on the Flow topic namespace' );
				return false;
			}

			$iterator->addConditions( [
				'page_namespace' => $namespaceId,
			] );
		} else {
			$iterator->addConditions( [
				'page_namespace != ' . NS_TOPIC,
			] );
		}

		$checkedCount = 0;
		$inconsistentCount = 0;

		// Not all of $inconsistentCount are fixable by the current script.
		$fixableInconsistentCount = 0;

		foreach ( $iterator as $rows ) {
			foreach ( $rows as $row ) {
				$checkedCount++;
				try {
					$result = $boardFixer->fix( $row->page_namesapce, $row->page_title, (int)$row->page_latest );
				} catch ( Exception $exception ) {
					$this->error( $exception->getMessage() );
					continue;
				}
				if ( $result['inconsistentCount'] === true ) {
					$inconsistentCount++;
				}
				if ( $result['fixableInconsistentCount'] === true ) {
					$fixableInconsistentCount++;
				}
				foreach ( $result['output'] as $output ) {
					$this->output( $output );
				}

				if ( $limit !== null && $fixableInconsistentCount >= $limit ) {
					break;
				}

			}

			$action = $dryRun ? 'identified as fixable' : 'fixed';
			$this->output( "\nChecked a total of $checkedCount Flow boards.  Of those, " .
				"$inconsistentCount boards had an inconsistent title; $fixableInconsistentCount " .
				"were $action.\n" );
			if ( $limit !== null && $fixableInconsistentCount >= $limit ) {
				break;
			}
		}
	}
}

$maintClass = 'FlowFixInconsistentBoards';
require_once RUN_MAINTENANCE_IF_MAIN;
