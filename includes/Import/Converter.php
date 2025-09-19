<?php

namespace Flow\Import;

use Flow\Exception\FlowException;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Exception\MWExceptionHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Article;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;
use Traversable;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Converts provided titles to Flow. This converter is idempotent when
 * used with an appropriate SourceStoreInterface, and may be run many times
 * without worry for duplicate imports.
 *
 * Flow does not currently support viewing the history of its page prior
 * to being flow enabled.  Because of this prior to conversion the current
 * wikitext page will be moved to an archive location.
 *
 * Implementing classes must choose a name for their archive page and
 * be able to create an IImportSource when provided a Title. On successful
 * import of a page a 'cleanup archive' edit is optionally performed.
 *
 * Any content changes to the imported content should be provided as part
 * of the IImportSource.
 */
class Converter {
	/**
	 * @var IReadableDatabase Primary database of the current wiki. Required
	 *  to lookup past page moves.
	 */
	protected $db;

	/**
	 * @var Importer Service capable of turning an IImportSource into
	 *  flow revisions.
	 */
	protected $importer;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var User The user for performing maintenance actions like moving
	 *  pages or editing templates onto an archived page. This should be
	 *  a system account and not a normal user.
	 */
	protected $user;

	/**
	 * @var IConversionStrategy Interface between this converter and an
	 *  IImportSource implementation.
	 */
	protected $strategy;

	/**
	 * @param IReadableDatabase $db Primary wiki database to read from
	 * @param Importer $importer
	 * @param LoggerInterface $logger
	 * @param User $user User for moves and edits related to the conversion process
	 * @param IConversionStrategy $strategy
	 *
	 * @throws ImportException When $user does not have an Id
	 */
	public function __construct(
		IReadableDatabase $db,
		Importer $importer,
		LoggerInterface $logger,
		User $user,
		IConversionStrategy $strategy
	) {
		if ( !$user->getId() ) {
			throw new ImportException( 'User must have id' );
		}
		$this->db = $db;
		$this->importer = $importer;
		$this->logger = $logger;
		$this->user = $user;
		$this->strategy = $strategy;

		$postprocessor = $strategy->getPostprocessor();
		if ( $postprocessor !== null ) {
			// @todo assert we cant cause duplicate postprocessors
			$this->importer->addPostprocessor( $postprocessor );
		}

		// Force the importer to use our logger for consistent output.
		$this->importer->setLogger( $logger );
	}

	/**
	 * Converts multiple pages into Flow boards
	 *
	 * @param Traversable<Title>|array $titles
	 * @param bool $dryRun If true, will not make any changes
	 * @param bool $convertEmpty Convert pages with no threads
	 */
	public function convertAll( $titles, $dryRun = false, $convertEmpty = false ) {
		/** @var Title $title */
		foreach ( $titles as $title ) {
			try {
				$this->convert( $title, $dryRun, $convertEmpty );
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->logger->error( "Exception while importing: {$title}" );
				$this->logger->error( (string)$e );
			}
		}
	}

	/**
	 * Converts a page into a Flow board
	 *
	 * @param Title $title
	 * @param bool $dryRun If true, will not make any changes
	 * @param bool $convertEmpty Convert pages with no threads
	 * @throws FlowException
	 */
	public function convert( Title $title, $dryRun = false, $convertEmpty = false ) {
		/*
		 * $title is the title we're currently considering to import.
		 * It could be a page we need to import, but could also e.g.
		 * be an archive page of a previous import run (in which case
		 * $movedFrom will be the Title object of that original page)
		 */
		$movedFrom = $this->getPageMovedFrom( $title );
		if ( $this->strategy->isConversionFinished( $title, $movedFrom ) ) {
			return;
		}

		if ( !$this->isAllowed( $title ) ) {
			throw new FlowException( "Not allowed to convert: {$title}" );
		}

		if ( !$convertEmpty ) {
			$article = new Article( $title );
			$pager = new \LqtDiscussionPager( $article, \TalkpageView::LQT_NEWEST_CHANGES );
			$pager->setLimit( 1 );
			if ( !$pager->getNumRows() ) {
				$this->logger->info( "Skipping {$title} as it has no LiquidThreads content" );
				return;
			}
		}

		if ( !$dryRun ) {
			$this->doConversion( $title, $movedFrom );
		} else {
			$this->logger->info( "Dry run: Would convert $title" );
		}
	}

	/**
	 * Returns a boolean indicating if we're allowed to import $title.
	 *
	 * @param Title $title
	 * @return bool
	 */
	protected function isAllowed( Title $title ) {
		// Only make changes to wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			$this->logger->warning( "WARNING: The title '" . $title->getPrefixedDBkey() .
				"' is being skipped because it has content model '" . $title->getContentModel() . "''." );
			return false;
		}

		if ( !$title->exists() ) {
			$this->logger->warning( "WARNING: The title '" . $title->getPrefixedDBkey() .
				"' is being skipped because it does not exist." );
			return false;
		}

		// At some point we may want to handle these, but for now just
		// let them be
		if ( $title->isRedirect() ) {
			$this->logger->warning( "WARNING: The title '" . $title->getPrefixedDBkey() .
				"' is being skipped because it is a redirect." );
			return false;
		}

		// Finally, check strategy-specific logic
		return $this->strategy->shouldConvert( $title );
	}

	protected function doConversion( Title $title, ?Title $movedFrom = null ) {
		if ( $movedFrom ) {
			// If the page is moved but has not completed conversion that
			// means the previous import failed to complete. Try again.
			$archiveTitle = $title;
			$title = $movedFrom;
			$this->logger->info( "Page previously archived from $title to $archiveTitle" );
		} else {
			// The move needs to happen prior to the import because upon starting the
			// import the top revision will be a flow-board revision.
			$archiveTitle = $this->strategy->decideArchiveTitle( $title );
			$this->logger->info( "Archiving page from $title to $archiveTitle" );
			$this->movePage( $title, $archiveTitle );

			$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
			// Wait for replicas to pick up the page move
			$lbFactory->waitForReplication();
		}

		$source = $this->strategy->createImportSource( $archiveTitle );
		if ( $this->importer->import( $source, $title, $this->user, $this->strategy->getSourceStore() ) ) {
			$this->createArchiveCleanupRevision( $title, $archiveTitle );
			$this->logger->info( "Completed import to $title from $archiveTitle" );
		} else {
			$this->logger->error( "Failed to complete import to $title from $archiveTitle" );
		}
	}

	/**
	 * Looks in the logging table to see if the provided title was last moved
	 * there by the user provided in the constructor. The provided user should
	 * be a system user for this task, as this assumes that user has never
	 * moved these pages outside the conversion process.
	 *
	 * This only considers the most recent move and not prior moves.  This allows
	 * for edge cases such as starting an import, canceling it, and manually
	 * reverting the move by a normal user.
	 *
	 * @param Title $title
	 * @return Title|null
	 */
	protected function getPageMovedFrom( Title $title ) {
		$row = $this->db->newSelectQueryBuilder()
			->select( [ 'log_namespace', 'log_title' ] )
			->from( 'logging' )
			->join( 'page', null, 'log_page = page_id' )
			->where( [
				'page_namespace' => $title->getNamespace(),
				'page_title' => $title->getDBkey(),
				'log_type' => 'move',
				'log_actor' => $this->user->getActorId()
			] )
			->caller( __METHOD__ )
			->orderBy( 'log_timestamp', SelectQueryBuilder::SORT_DESC )
			->fetchRow();

		// The page has never been moved or the most recent move was not by our user
		if ( !$row ) {
			return null;
		}

		return Title::makeTitle( $row->log_namespace, $row->log_title );
	}

	/**
	 * Moves the source page to the destination. Does not leave behind a
	 * redirect, intending that flow will place a revision there for its new
	 * board.
	 *
	 * @param Title $from
	 * @param Title $to
	 * @throws ImportException on failed import
	 */
	protected function movePage( Title $from, Title $to ) {
		$mp = MediaWikiServices::getInstance()
			->getMovePageFactory()
			->newMovePage( $from, $to );

		$valid = $mp->isValidMove();
		if ( !$valid->isOK() ) {
			$this->logger->error( $valid->getMessage()->text() );
			throw new ImportException( "It is not valid to move {$from} to {$to}" );
		}

		// Note that this comment must match the regex in self::getPageMovedFrom
		$status = $mp->move(
			/* user */ $this->user,
			/* reason */ $this->strategy->getMoveComment( $from, $to ),
			/* create redirect */ false
		);

		if ( !$status->isGood() ) {
			$this->logger->error( $status->getMessage()->text() );
			throw new ImportException( "Failed moving {$from} to {$to}" );
		}
	}

	/**
	 * Creates a new revision of the archived page with strategy-specific changes.
	 *
	 * @param Title $title Previous location of the page, before moving
	 * @param Title $archiveTitle Current location of the page, after moving
	 * @throws ImportException
	 */
	protected function createArchiveCleanupRevision( Title $title, Title $archiveTitle ) {
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $archiveTitle );
		// doUserEditContent will do this anyway, but we need to now for the revision.
		$page->loadPageData( IDBAccessObject::READ_LATEST );
		$revision = $page->getRevisionRecord();
		if ( $revision === null ) {
			throw new ImportException( "Expected a revision at {$archiveTitle}" );
		}

		// Do not create revisions based on rev_deleted revisions.
		$content = $revision->getContent( SlotRecord::MAIN, RevisionRecord::FOR_PUBLIC );
		if ( !$content instanceof WikitextContent ) {
			throw new ImportException( "Expected wikitext content at: {$archiveTitle}" );
		}

		$newContent = $this->strategy->createArchiveCleanupRevisionContent( $content, $title );
		if ( $newContent === null ) {
			return;
		}

		$status = $page->doUserEditContent(
			$newContent,
			$this->user,
			$this->strategy->getCleanupComment( $title, $archiveTitle ),
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC
		);

		if ( !$status->isGood() ) {
			$this->logger->error( $status->getMessage()->text() );
			throw new ImportException( "Failed creating archive cleanup revision at {$archiveTitle}" );
		}
	}
}
