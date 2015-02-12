<?php

namespace Flow\Import;

use DatabaseBase;
use Flow\Repository\TitleRepository;
use MovePage;
use MWExceptionHandler;
use Psr\Log\LoggerInterface;
use Revision;
use Title;
use User;
use WikiPage;
use WikitextContent;

/**
 * Converts provided titles to Flow. This converter is idempotent when
 * used with an appropriate ImportSourceStore, and may be run many times
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
	 * @var DatabaseBase Slave database of the current wiki. Required
	 *  to lookup past page moves.
	 */
	protected $dbr;

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
	 * @param DatabaseBase $dbr Slave wiki database to read from
	 * @param Importer $importer
	 * @param LoggerInterface $logger
	 * @param User $user Administrative user for moves and edits related
	 *  to the conversion process.
	 * @param IConversionStrategy $strategy
	 * @throws ImportException When $user does not have an Id
	 */
	public function __construct(
		DatabaseBase $dbr,
		Importer $importer,
		LoggerInterface $logger,
		User $user,
		IConversionStrategy $strategy
	) {
		if ( !$user->getId() ) {
			throw new ImportException( 'User must have id' );
		}
		$this->dbr = $dbr;
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
	 * @param Traversable<Title> $titles
	 */
	public function convert( $titles ) {
		/** @var Title $title */
		foreach ( $titles as $title ) {
			try {
				$movedFrom = $this->getPageMovedFrom( $title );
				if ( ! $this->isAllowed( $title, $movedFrom ) ) {
					continue;
				}

				if ( $this->strategy->isConversionFinished( $title, $movedFrom ) ) {
					continue;
				}

				$this->doConversion( $title, $movedFrom );
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->logger->error( "Exception while importing: {$title}" );
				$this->logger->error( (string)$e );
			}
		}
	}

	protected function isAllowed( Title $title, Title $movedFrom = null ) {
		// Only make changes to wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}

		// At some point we may want to handle these, but for now just
		// let them be
		if ( $title->isRedirect() ) {
			return false;
		}

		// If we previously moved this page, continue the import
		if ( $movedFrom !== null ) {
			return true;
		}

		// Don't allow conversion of sub pages unless it is
		// a talk page with matching subject page. For example
		// we will convert User_talk:Foo/bar only if User:Foo/bar
		// exists, and we will never convert User:Baz/bang.
		if ( $title->isSubPage() && ( !$title->isTalkPage() || !$title->getSubjectPage()->exists() ) ) {
			return false;
		}

		return true;
	}

	protected function doConversion( Title $title, Title $movedFrom = null ) {
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
		}

		$source = $this->strategy->createImportSource( $archiveTitle );
		if ( $this->importer->import( $source, $title, $this->strategy->getSourceStore() ) ) {
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
		$row = $this->dbr->selectRow(
			array( 'logging', 'page' ),
			array( 'log_namespace', 'log_title', 'log_user' ),
			array(
				'page_namespace' => $title->getNamespace(),
				'page_title' => $title->getDBkey(),
				'log_page = page_id',
				'log_type' => 'move',
			),
			__METHOD__,
			array(
				'LIMIT' => 1,
				'ORDER BY' => 'log_timestamp DESC'
			)
		);

		// The page has never been moved
		if ( !$row ) {
			return null;
		}

		// The most recent move was not by our user
		if ( $row->log_user != $this->user->getId() ) {
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
		$mp = new MovePage( $from, $to );
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
	 * Creates a new revision of the archived page that strips the LQT magic word
	 * and injects a template about the move. With the magic word stripped these pages
	 * will no longer contain the use-liquid-threads page property and will effectively
	 * no longer be lqt pages.
	 *
	 * @param Title $title Previous location of the page, before moving
	 * @param Title $archiveTitle Current location of the page, after moving
	 * @throws ImportException
	 */
	protected function createArchiveCleanupRevision( Title $title, Title $archiveTitle ) {
		$page = WikiPage::factory( $archiveTitle );
		$revision = $page->getRevision();
		if ( $revision === null ) {
			throw new ImportException( "Expected a revision at {$archiveTitle}" );
		}

		// Do not create revisions based on rev_deleted revisions.
		$content = $revision->getContent( Revision::FOR_PUBLIC );
		if ( !$content instanceof WikitextContent ) {
			throw new ImportException( "Expected wikitext content at: {$archiveTitle}" );
		}

		$newContent = $this->strategy->createArchiveCleanupRevisionContent( $content, $title );
		if ( $newContent === null ) {
			return;
		}

		$status = $page->doEditContent(
			$newContent,
			$this->strategy->getCleanupComment( $title, $archiveTitle ),
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->user
		);

		if ( !$status->isGood() ) {
			$this->logger->error( $status->getMessage()->text() );
			throw new ImportException( "Failed creating archive cleanup revision at {$archiveTitle}" );
		}
	}

	/**
	 * Helper method decides on an archive title based on a set of printf formats.
	 * Each format should first have a %s for the base page name and a %d for the
	 * archive page number. Example:
	 *
	 *   %s/Archive %d
	 *
	 * It will iterate through the formats looking for an existing format.  If no
	 * formats are currently in use the first format will be returned with n=1.
	 * If a format is currently in used we will look for the first unused page
	 * >= to n=1 and <= to n=20.
	 *
	 * @param Title $source
	 * @param string[] $formats
	 * @param TitleRepository|null $titleRepo
	 * @return Title
	 * @throws ImportException
	 */
	static public function decideArchiveTitle( Title $source, array $formats, TitleRepository $titleRepo = null ) {
		if ( $titleRepo === null ) {
			$titleRepo = new TitleRepository();
		}

		$format = false;
		$n = 1;
		$text = $source->getPrefixedText();
		foreach ( $formats as $potential ) {
			$title = Title::newFromText( sprintf( $potential, $text, $n ) );
			if ( $title && $titleRepo->exists( $title ) ) {
				$format = $potential;
				break;
			}
		}
		if ( $format === false ) {
			// assumes this creates a valid title
			return Title::newFromText( sprintf( $formats[0], $text, $n ) );
		}

		for ( $n = 2; $n <= 20; ++$n ) {
			$title = Title::newFromText( sprintf( $format, $text, $n ) );
			if ( $title && !$titleRepo->exists( $title ) ) {
				return $title;
			}
		}

		throw new ImportException( "All titles 1 through 20 (inclusive) exist for format: $format" );
	}
}
