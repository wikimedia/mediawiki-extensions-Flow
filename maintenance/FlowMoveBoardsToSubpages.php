<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use Flow\Container;
use Flow\DbFactory;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDBAccessObject;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Moves pages that contain Flow boards to a subpage of their current location
 *
 * There is a dry run available.
 *
 * @ingroup Maintenance
 */
class FlowMoveBoardsToSubpages extends Maintenance {
	protected DbFactory $dbFactory;

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Moves pages that contain Flow boards to a subpage of their current location. ' .
			'Must be run separately for each affected wiki.' );

		$this->addOption( 'dry-run', 'Only prints the board names, without changing anything.' );
		$this->addOption( 'namespaceName', 'Name of namespace to check, otherwise all', false, true );
		$this->addOption( 'limit', 'Limit of inconsistent pages to identify (and fix if not a dry ' .
			'run). Defaults to no limit', false, true );
		$this->addOption( 'subpage', 'Name of subpage to create. Defaults to "Flow"', false, true );
		$this->addOption( 'always-number', 'Always number an archive page, even when it\'s the first one' );

		$this->setBatchSize( 300 );

		$this->requireExtension( 'Flow' );
	}

	/**
	 * @return false|void
	 */
	public function execute() {
		global $wgLang;

		$this->dbFactory = Container::get( 'db.factory' );

		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$movePageFactory = MediaWikiServices::getInstance()->getMovePageFactory();
		$moveUser = $occupationController->getTalkpageManager();

		$dryRun = $this->hasOption( 'dry-run' );

		$limit = $this->getOption( 'limit' );

		$subpage = $this->getOption( 'subpage', 'Flow' );

		$wikiDbw = $this->dbFactory->getWikiDB( DB_PRIMARY );

		$iterator = new BatchRowIterator( $wikiDbw, 'page', 'page_id', $this->getBatchSize() );
		$iterator->setFetchColumns( [ 'page_namespace', 'page_title', 'page_latest' ] );
		$iterator->addConditions( [
			'page_content_model' => CONTENT_MODEL_FLOW_BOARD,
		] );
		$iterator->setCaller( __METHOD__ );

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
				$wikiDbw->expr( 'page_namespace', '!=', NS_TOPIC ),
			] );
		}

		$checkedCount = 0;
		$moveCount = 0;

		foreach ( $iterator as $rows ) {
			foreach ( $rows as $row ) {
				$checkedCount++;
				$coreTitle = Title::makeTitle( $row->page_namespace, $row->page_title );

				if ( preg_match( "/\/([Ff]low|[Aa]rchive|StructuredDiscussions).*/", $coreTitle->getText() ) ) {
					// Don't try to act on subpages
					// $coreTitle->isSubpage() only works on namespaces with subpages enabled, which
					// we don't care about for this check.
					$this->output( "Skipped '$coreTitle' as it is already an archived page\n" );
					continue;
				}
				// $row / $coreTitle is a page with the flow board content model, and isn't an archived page
				if ( !$coreTitle->inNamespace( NS_USER_TALK ) ) {
					// Skip pages whose non-talk namespace counterpart doesn't exist, assuming they're archives
					// but don't skip user talk pages, since having a redlinked user page isn't indicative of anything
					$subject = $coreTitle->getSubjectPage();
					if ( !$subject || !$subject->exists() ) {
						$this->output( "Skipped '$coreTitle' as it has no associated subject page\n" );
						continue;
					}

					if ( $coreTitle->equals( $subject ) ) {
						$this->output( "Skipped '$coreTitle' as it is a standalone Flow page\n" );
						continue;
					}
				}

				$creationStatus = $this->findValidSubpage( $coreTitle, $subpage, $moveUser );

				if ( !$creationStatus->isOk() ) {
					$this->error( "Cannot move '$coreTitle': " . $creationStatus->getMessage()->text() . "\n" );
					continue;
				}

				$subpageTitle = $creationStatus->getValue();

				if ( $dryRun ) {
					$moveCount++;
					$this->output( "Would move '$coreTitle' to '$subpageTitle'\n" );
				} else {
					$mp = $movePageFactory->newMovePage( $coreTitle, $subpageTitle );

					$status = $mp->move(
						/* user */ $moveUser,
						/* reason */ "Flow archival",
						/* create redirect */ false
					);

					if ( $status->isGood() ) {
						$moveCount++;
						$this->output( "Moved '$coreTitle' to '$subpageTitle'\n" );
						$stubStatus = $this->createStubPage( $coreTitle, $subpageTitle, $moveUser );
						if ( $stubStatus->isGood() ) {
							$this->output( "Created stub at '$coreTitle'\n" );
						} else {
							$this->error( "Failed to create stub at '$coreTitle': " . $status->getMessage()->text() . "\n" );
						}
					} else {
						$this->error( "Failed to move '$coreTitle' to '$subpageTitle': " . $status->getMessage()->text() . "\n" );
					}
				}

				if ( $limit !== null && $moveCount >= $limit ) {
					break;
				}
			}

			$action = $dryRun ? 'would have been moved' : 'were moved';
			$this->output( "\nChecked a total of $checkedCount pages. Of those, " .
				"$moveCount pages $action.\n" );

			if ( $limit !== null && $moveCount >= $limit ) {
				break;
			}
		}
	}

	/**
	 * Checks for a valid subpage to move a board into
	 *
	 * @param Title $coreTitle Previous location of the page, before moving
	 * @param string $subpage Name stem for the subpages
	 * @param User $moveUser User to make the move
	 * @return Status Contains subpage as value
	 */
	protected function findValidSubpage( Title $coreTitle, string $subpage, User $moveUser ) {
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );

		$status = Status::newGood();

		$alwaysNumber = $this->getOption( 'always-number' );

		for ( $i = 1; $i <= 9; $i++ ) {
			$suffix = ( $alwaysNumber || $i > 1 ) ? $i : '';
			$subpageTitle = $coreTitle->getSubpage( $subpage . $suffix );

			$creationStatus = $occupationController->safeAllowCreation(
				$subpageTitle,
				$moveUser,
				/* $mustNotExist = */ true,
				/* $forWrite = */ true
			);

			if ( $creationStatus->isGood() ) {
				$status->setResult( true, $subpageTitle );
				return $status;
			}

			$status->merge( $creationStatus );
		}

		return $status;
	}

	/**
	 * Creates a new revision of the archived page with strategy-specific changes.
	 *
	 * @param Title $title Previous location of the page, before moving
	 * @param Title $archiveTitle Current location of the page, after moving
	 * @param User $user
	 * @return Status
	 */
	protected function createStubPage( Title $title, Title $archiveTitle, User $user ) {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		// doUserEditContent will do this anyway, but we need to now for the revision.
		$page->loadPageData( IDBAccessObject::READ_LATEST );

		$status = $page->doUserEditContent(
			new WikitextContent( "* [[{$archiveTitle->getPrefixedText()}]]" ),
			$user,
			"Flow archival",
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC
		);

		return $status;
	}
}

$maintClass = FlowMoveBoardsToSubpages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
