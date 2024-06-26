<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\DbFactory;
use Flow\Import\ArchiveNameHelper;
use Flow\OccupationController;
use Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\ActorMigration;
use MediaWiki\User\User;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class FlowRestoreLQT extends Maintenance {
	/**
	 * @var User
	 */
	protected $talkpageManagerUser;

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var bool
	 */
	protected $dryRun = false;

	/**
	 * @var bool
	 */
	protected $overwrite = false;

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Restores LQT boards after a Flow conversion (revert LQT conversion ' .
			'edits & move LQT boards back)' );

		$this->addOption( 'dryrun', 'Simulate script run, without making actual changes' );
		$this->addOption( 'overwrite-flow', 'Removes the Flow board entirely, restoring LQT to ' .
			'its original location' );

		$this->setBatchSize( 1 );

		$this->requireExtension( 'Flow' );
	}

	public function execute() {
		/** @var OccupationController $occupationController */
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$this->talkpageManagerUser = $occupationController->getTalkpageManager();
		$this->dbFactory = Container::get( 'db.factory' );
		$this->dryRun = $this->getOption( 'dryrun', false );
		$this->overwrite = $this->getOption( 'overwrite-flow', false );

		$this->output( "Restoring posts...\n" );
		$this->restoreLQTThreads();

		$this->output( "Restoring boards...\n" );
		$this->restoreLQTBoards();
	}

	/**
	 * During an import, LQT boards are moved out of the way (archived) to make
	 * place for the Flow board.
	 * And after completing an import, LQT boards are disabled with
	 * {{#useliquidthreads:0}}
	 * That's all perfectly fine assuming the conversion goes well, but we'll
	 * want to go back to the original content with this script...
	 */
	protected function restoreLQTBoards() {
		$dbr = $this->dbFactory->getWikiDB( DB_REPLICA );
		$batchSize = $this->getBatchSize();

		$revWhere = ActorMigration::newMigration()
			->getWhere( $dbr, 'rev_user', $this->talkpageManagerUser );

		$lbFactory = $this->getServiceContainer()->getDBLoadBalancerFactory();

		foreach ( $revWhere['orconds'] as $revCond ) {
			$startId = 0;
			do {
				// fetch all LQT boards that have been moved out of the way,
				// with their original title & their current title
				$rows = $dbr->newSelectQueryBuilder()
					// log_namespace & log_title will be the original location
					// page_namespace & page_title will be the current location
					// rev_id is the first Flow talk page manager edit id
					// log_id is the log entry for when importer moved LQT page
					->select( [ 'log_namespace', 'log_title', 'page_id', 'page_namespace', 'page_title',
						'rev_id' => 'MIN(rev_id)', 'log_id' ] )
					->from( 'logging' )
					->join( 'page', null, 'page_id = log_page' )
					->join( 'revision', null, 'rev_page = log_page' )
					->tables( $revWhere['tables'] )
					->where( [
						'log_actor' => $this->talkpageManagerUser->getActorId(),
						'log_type' => 'move',
						'page_content_model' => 'wikitext',
						$dbr->expr( 'page_id', '>', $startId ),
						$revCond,
					] )
					->groupBy( 'rev_page' )
					->limit( $batchSize )
					->orderBy( 'log_id' )
					->joinConds( $revWhere['joins'] )
					->caller( __METHOD__ )
					->fetchResultSet();

				foreach ( $rows as $row ) {
					$from = Title::newFromText( $row->page_title, $row->page_namespace );
					$to = Title::newFromText( $row->log_title, $row->log_namespace );

					// undo {{#useliquidthreads:0}}
					$this->restorePageRevision( $row->page_id, $row->rev_id );
					// undo page move to archive location
					$this->restoreLQTPage( $from, $to, $row->log_id );

					$startId = $row->page_id;
				}

				$lbFactory->waitForReplication();
			} while ( $rows->numRows() >= $batchSize );
		}
	}

	/**
	 * After converting an LQT thread to Flow, it's content is altered to
	 * redirect to the new Flow topic.
	 * This finds all last original revisions & restores them.
	 */
	protected function restoreLQTThreads() {
		$dbr = $this->dbFactory->getWikiDB( DB_REPLICA );
		$batchSize = $this->getBatchSize();

		$revWhere = ActorMigration::newMigration()
			->getWhere( $dbr, 'rev_user', $this->talkpageManagerUser );

		$lbFactory = $this->getServiceContainer()->getDBLoadBalancerFactory();

		foreach ( $revWhere['orconds'] as $revCond ) {
			$startId = 0;
			do {
				// for every LQT post, find the first edit by Flow talk page manager
				// (to redirect to the new Flow copy)
				$rows = $dbr->newSelectQueryBuilder()
					->select( [ 'rev_page', 'rev_id' => ' MIN(rev_id)' ] )
					->from( 'page' )
					->join( 'revision', null, 'rev_page = page_id' )
					->tables( $revWhere['tables'] )
					->where( [
						'page_namespace' => [ NS_LQT_THREAD, NS_LQT_SUMMARY ],
						$revCond,
						$dbr->expr( 'page_id', '>', $startId ),
					] )
					->groupBy( 'page_id' )
					->limit( $batchSize )
					->orderBy( 'page_id' )
					->joinConds( $revWhere['joins'] )
					->caller( __METHOD__ )
					->fetchResultSet();

				foreach ( $rows as $row ) {
					// undo #REDIRECT edit
					$this->restorePageRevision( $row->rev_page, $row->rev_id );
					$startId = $row->rev_page;
				}

				$lbFactory->waitForReplication();
			} while ( $rows->numRows() >= $batchSize );
		}
	}

	/**
	 * @param Title $lqt Title of the LQT board
	 * @param Title $flow Title of the Flow board
	 * @param int $logId Log id for when LQT board was moved by import
	 * @return Status
	 */
	protected function restoreLQTPage( Title $lqt, Title $flow, $logId ) {
		if ( $lqt->equals( $flow ) ) {
			// is at correct location already (probably a rerun of this script)
			return Status::newGood();
		}

		$archiveNameHelper = new ArchiveNameHelper();

		if ( !$flow->exists() ) {
			$this->movePage( $lqt, $flow, '/* Restore LQT board to original location */' );
		} else {
			/*
			 * The importer will query the log table to find the LQT archive
			 * location. It will assume that Flow talk page manager moved the
			 * LQT board to its archive location, and will not recognize the
			 * board if it's been moved by someone else.
			 * Because of that feature (yes, that is intended), we need to make
			 * sure that - in order to enable LQT imports to be picked up again
			 * after this - the move from <original page> to <archive page>
			 * happens in 1 go, by Flow talk page manager.
			 */
			if ( !$this->overwrite ) {
				/*
				 * Before we go moving pages around like crazy, let's see if we
				 * actually need to. While it's certainly possible that the LQT
				 * pages have been moved since the import and we need to fix
				 * them, it's very likely that they haven't. In that case, we
				 * won't have to do the complex moves.
				 */
				$dbr = $this->dbFactory->getWikiDB( DB_REPLICA );
				$count = $dbr->newSelectQueryBuilder()
					->select( '*' )
					->from( 'logging' )
					->where( [
						'log_page' => $lqt->getArticleID(),
						'log_type' => 'move',
						$dbr->expr( 'log_id', '>', $logId ),
					] )
					->caller( __METHOD__ )
					->fetchRowCount();

				if ( $count > 0 ) {
					$this->output( "Ensuring LQT board '{$lqt->getPrefixedDBkey()}' is " .
						"recognized as archive of Flow board '{$flow->getPrefixedDBkey()}'.\n" );

					// 1: move Flow board out of the way so we can restore LQT to
					// its original location
					$archive = $archiveNameHelper->decideArchiveTitle( $flow, [ '%s/Flow Archive %d' ] );
					$this->movePage( $flow, $archive, '/* Make place to restore LQT board */' );

					// 2: move LQT board to the original location
					$this->movePage( $lqt, $flow, '/* Restore LQT board to original location */' );

					// 3: move LQT board back to archive location
					$this->movePage( $flow, $lqt, '/* Restore LQT board to archive location */' );

					// 4: move Flow board back to the original location
					$this->movePage( $archive, $flow, '/* Restore Flow board to correct location */' );
				}
			} else {
				$this->output( "Deleting '{$flow->getPrefixedDBkey()}' & moving " .
					"'{$lqt->getPrefixedDBkey()}' there.\n" );

				if ( !$this->dryRun ) {
					$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $flow );
					$page->doDeleteArticleReal(
						'/* Make place to restore LQT board */',
						$this->talkpageManagerUser,
						false,
						null,
						$error,
						null,
						[],
						'delete',
						true
					);
				}

				$this->movePage( $lqt, $flow, '/* Restore LQT board to original location */' );
			}
		}
	}

	/**
	 * @param Title $from
	 * @param Title $to
	 * @param string $reason
	 * @return Status
	 */
	protected function movePage( Title $from, Title $to, $reason ) {
		$this->output( "	Moving '{$from->getPrefixedDBkey()}' to '{$to->getPrefixedDBkey()}'.\n" );

		$movePage = $this->getServiceContainer()
			->getMovePageFactory()
			->newMovePage( $from, $to );
		$status = $movePage->isValidMove();
		if ( !$status->isGood() ) {
			return $status;
		}

		if ( $this->dryRun ) {
			return Status::newGood();
		}

		return $movePage->move( $this->talkpageManagerUser, $reason, false );
	}

	/**
	 * @param int $pageId
	 * @param int $nextRevisionId Revision of the first *bad* revision
	 * @return Status
	 */
	protected function restorePageRevision( $pageId, $nextRevisionId ) {
		global $wgLang;

		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromID( $pageId );
		$revisionLookup = $this->getServiceContainer()->getRevisionLookup();
		$nextRevision = $revisionLookup->getRevisionById( $nextRevisionId );
		$revision = $revisionLookup->getPreviousRevision( $nextRevision );
		$mainContent = $revision->getContent( SlotRecord::MAIN, RevisionRecord::RAW );
		'@phan-var \Content $mainContent';

		if ( $page->getContent()->equals( $mainContent ) ) {
			// has correct content already (probably a rerun of this script)
			return Status::newGood();
		}

		$content = $mainContent->serialize();
		$content = $wgLang->truncateForVisual( $content, 150 );
		$content = str_replace( "\n", '\n', $content );
		$this->output( "Restoring revision {$revision->getId()} for LQT page {$pageId}: {$content}\n" );

		if ( $this->dryRun ) {
			return Status::newGood();
		} else {
			return $page->doUserEditContent(
				$mainContent,
				$this->talkpageManagerUser,
				'/* Restore LQT topic content */',
				EDIT_UPDATE | EDIT_MINOR | EDIT_FORCE_BOT,
				$revision->getId()
			);
		}
	}
}

$maintClass = FlowRestoreLQT::class;
require_once RUN_MAINTENANCE_IF_MAIN;
