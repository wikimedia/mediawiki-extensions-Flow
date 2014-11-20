<?php

namespace Flow\Import;

use DatabaseBase;
use Flow\Exception\FlowException;
use Flow\Import\Importer;
use Flow\Import\InamemportSourceStore;
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
 * be able to create an IImportSource when provided a Title. On successfull
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
	 */
	public function __construct(
		DatabaseBase $dbr,
		Importer $importer,
		LoggerInterface $logger,
		User $user,
		IConversionStrategy $strategy
	) {
		if ( !$user->getId() ) {
			throw new \Exception( 'User must have id' );
		}
		$this->dbr = $dbr;
		$this->importer = $importer;
		$this->logger = $logger;
		$this->user = $user;
		$this->strategy = $strategy;
	}

	/**
	 * @param Traversable<Title> $titles
	 */
	public function convert( $titles ) {
		/** @var Title $title */
		foreach ( $titles as $title ) {
			try {
				if ( ! $this->isAllowed( $title ) ) {
					continue;
				}

				// Only convert sub pages if we made them sub pages
				$movedFrom = $this->getPageMovedFrom( $title );
				if ( $movedFrom === null && $title->isSubpage() ) {
					continue;
				}

				if ( $this->strategy->isConversionFinished( $title, $movedFrom ) ) {
					continue;
				}

				$this->doConversion( $title, $movedFrom );
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->logger->error( "Exception while importing: {$title->getPrefixedText()}" );
				$this->logger->error( (string)$e );
			}
		}
	}

	protected function isAllowed( Title $title ) {
		// Only make changes to wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}

		// At some point we may want to handle these, but for now just
		// let them be
		if ( $title->isRedirect() ) {
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
			$titleText = $title->getPrefixedText();
			$archiveTitleText = $archiveTitle->getPrefixedText();
			$this->logger->info( "Page previously archived from $titleText to $archiveTitleText" );
		} else {
			// The move needs to happen prior to the import because upon starting the
			// import the top revision will be a flow-board revision.
			$archiveTitle = $this->strategy->decideArchiveTitle( $title );
			$archiveTitleText = $archiveTitle->getPrefixedText();
			if ( !$archiveTitle->isSubpage() ) {
				throw new FlowException( 'Archive title is not a subpage: ' . $archiveTitleText );
			}
			$titleText = $title->getPrefixedText();
			$this->logger->info( "Archiving page from $titleText to $archiveTitleText" );
			$this->movePage( $title, $archiveTitle );
		}


		$source = $this->strategy->createImportSource( $archiveTitle );
		if ( $this->importer->import( $source, $title, $this->strategy->getSourceStore() ) ) {
			$this->createArchiveCleanupRevision( $title, $archiveTitle );
			$this->logger->info( "Completed import to $titleText from $archiveTitleText" );
		} else {
			$this->logger->error( "Failed to complete import to $titleText from $archiveTitleText" );
		}
	}

	/**
	 * Looks in the logging table to see if the provided title was moved
	 * there by the user provided in the constructor. The provided user should
	 * be a system user for this task, as this assumes that user has never
	 * moved these pages outside the conversion process.
	 *
	 * @param Title $title
	 * @return Title|null
	 */
	protected function getPageMovedFrom( Title $title ) {
		$row = $this->dbr->selectRow(
			array( 'logging', 'page' ),
			array( 'log_namespace', 'log_title' ),
			array(
				'page_namespace' => $title->getNamespace(),
				'page_title' => $title->getDBkey(),
				'log_page = page_id',
				'log_user' => $this->user->getId(),
				'log_type' => 'move',
			),
			__METHOD__,
			array(
				'LIMIT' => 1,
				'ORDER BY' => 'log_timestamp DESC'
			)
		);

		if ( $row ) {
			return Title::makeTitle( $row->log_namespace, $row->log_title );
		} else {
			return null;
		}
	}

	/**
	 * Moves the source page to the destination. Does not leave behind a
	 * redirect, intending that flow will place a revision there for its new
	 * board.
	 *
	 * @param Title $from
	 * @param Title $to
	 * @throws FlowException on failed import
	 */
	protected function movePage( Title $from, Title $to ) {
		$mp = new MovePage( $from, $to );
		$valid = $mp->isValidMove();
		if ( !$valid->isOK() ) {
			throw new FlowException( "It is not valid to move {$from->getPrefixedText()} to {$to->getPrefixedText()}" );
		}

		// Note that this comment must match the regex in self::getPageMovedFrom
		$status = $mp->move(
			/* user */ $this->user,
			/* reason */ $this->strategy->getMoveComment( $from, $to ),
			/* create redirect */ false
		);

		if ( !$status->isGood() ) {
			throw new FlowException( "Failed moving {$from->getPrefixedText()} to {$to->getPrefixedText()}" );
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
	 * @throws FlowException
	 */
	protected function createArchiveCleanupRevision( Title $title, Title $archiveTitle ) {
		$page = WikiPage::factory( $archiveTitle );
		$revision = $page->getRevision();
		if ( $revision === null ) {
			throw new FlowException( "Expected a revision at {$archiveTitle->getPrefixedText()}" );
		}

		$content = $revision->getContent( Revision::RAW );
		if ( !$content instanceof WikitextContent ) {
			throw new FlowException( "Expected wikitext content at: {$archiveTitle->getPrefixedText()}" );
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
			throw new FlowException( "Failed creating archive cleanup revision at {$archiveTitle->getPrefixedText()}" );
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
	 * between n=2 and n=20.
	 *
	 * @param Title $source
	 * @param string[] $formats
	 * @param TitleRepository|null $titleRepo
	 * @return Title
	 * @throws FlowException
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
		for ( ++$n; $n < 20; ++$n ) {
			$title = Title::newFromText( sprintf( $format, $text, $n ) );
			if ( $title && !$titleRepo->exists( $title ) ) {
				return $title;
			}
		}

		throw new FlowException( "All titles 1 through 20 exist for format: $format" );
	}
}
