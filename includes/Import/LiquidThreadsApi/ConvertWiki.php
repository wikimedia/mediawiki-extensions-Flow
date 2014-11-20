<?php

namespace Flow\Import\LiquidThreadsApi;

use DateTime;
use DateTimeZone;
use Flow\Exception\FlowException;
use Flow\Import\Importer;
use Flow\Import\ImportSourceStore;
use Flow\Utils\PagesWithPropertyIterator;
use MovePage;
use MWExceptionHandler;
use Psr\Log\LoggerInterface;
use Revision;
use Status;
use Title;
use User;
use WikiPage;
use WikitextContent;

/**
 * Converts all LiquidThreads pages on a wiki to Flow. This converter is idempotent
 * when used with an appropriate ImportSourceStore, and may be run many times
 * without worry for duplicate imports.
 *
 * Pages with the LQT magic word will be moved to a subpage of their original location
 * named 'LQT Archive N' with N increasing starting at 1 looking for the first empty page.
 * On successfull import of an entire page the LQT magic word will be stripped from the
 * archive version of the page.
 */
class ConvertWiki {
	/**
	 * @var Importer
	 */
	protected $importer;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var ImportSourceStore
	 */
	protected $sourceStore;

	/**
	 * @var ApiBackend
	 */
	protected $api;

	/**
	 * @var User The user for performing maintenance actions like moving pages or
	 *  adding templates to an archived page. This should be a system account and
	 *  not a normal user.
	 */
	protected $user;

	public function __construct(
		Importer $importer,
		LoggerInterface $logger,
		ImportSourceStore $sourceStore,
		ApiBackend $api,
		User $user
	) {
		if ( !$user->getId() ) {
			throw new \Exception( 'User must have id' );
		}
		$this->importer = $importer;
		$this->logger = $logger;
		$this->sourceStore = $sourceStore;
		$this->api = $api;
		$this->user = $user;
	}

	public function convert() {
		$titles = new PagesWithPropertyIterator(
			wfGetDB( DB_SLAVE ),
			'use-liquid-threads'
		);
		/** @var Title $title */
		foreach ( $titles as $title ) {
			$movedFrom = $this->getPageMovedFrom( $title );
			if ( $movedFrom === null && $title->isSubpage() ) {
				continue;
			}
			try {
				if ( $movedFrom ) {
					// If the page is moved but still retains the use-liquid-threads page prop
					// that means the previous import failed to complete.  Try again.
					$archiveTitle = $title;
					$title = $movedFrom;
					$titleText = $title->getPrefixedText();
					$archiveTitleText = $archiveTitle->getPrefixedText();
					$this->logger->info( "Page previously archived from $titleText to $archiveTitleText" );
				} else {
					// The move needs to happen prior to the import because upon starting the
					// import the top revision will be a flow-board revision.
					$archiveTitle = $this->decideArchiveTitle( $title );
					$titleText = $title->getPrefixedText();
					$archiveTitleText = $archiveTitle->getPrefixedText();
					$this->logger->info( "Archiving page from $titleText to $archiveTitleText" );
					$this->movePage( $title, $archiveTitle );
				}

				$this->logger->info( "Importing page: $titleText" );
				$source = new ImportSource( $this->api, $archiveTitleText );
				// As long as we don't strip the lqt magic word the script will
				// keep trying on future runs to import the page idempotently
				// until it finishes.
				if ( $this->importer->import( $source, $title, $this->sourceStore ) ) {
					$this->createArchiveCleanupRevision( $title, $archiveTitle );
				} else {
					$this->logger->error( "Failed to complete import to $titleText from $archiveTitleText" );
				}
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->logger->error( 'Exception while importing: ' . $title->getPrefixedText() );
				$this->logger->error( (string)$e );
			}
		}
	}

	/**
	 * Looks in the logging table to see if the provided title was moved
	 * there by the user provided in the constructor. The provided user should
	 * be a system user for this task, as this assumes that user has never
	 * moved these LQT pages outside the conversion process.
	 *
	 * @param Title $title
	 * @return Title|null
	 */
	protected function getPageMovedFrom( Title $title ) {
		$row = wfGetDb( DB_SLAVE )->selectRow(
			'logging',
			array( 'log_namespace', 'log_title' ),
			array(
				'log_page' => WikiPage::factory( $title )->getId(),
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
	 * Moves the source page to the destination.  Does not leave behind a
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
			/* reason */ "Conversion of LQT to Flow from: {$from->getPrefixedText()}",
			/* create redirect */ false
		);

		if ( !$status->isGood() ) {
			throw new FlowException( "Failed moving {$from->getPrefixedText()} to {$to->getPrefixedText()}" );
		}
	}

	/**
	 * Flow does not support viewing the history of the wikitext pages it takes
	 * over, so those need to be moved out the way. This method decides that
	 * destination. The archived revisions include the headers displayed with
	 * lqt and potentially any pre-lqt wikitext talk page content.
	 *
	 * @param Title $source
	 * @return Title
	 * @throws FlowException When no title can be decided upon
	 */
	protected function decideArchiveTitle( Title $source ) {
		$format = $source->getPrefixedText() . "/LQT Archive %d";
		for ( $n = 1; $n < 20; ++$n ) {
			$title = Title::newFromText( sprintf( $format, $n ) );
			if ( $title && !$title->exists() ) {
				return $title;
			}
		}

		throw new FlowException( "All titles 1 through 20 exist for format: $format" );
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
			throw new FlowException( 'Expected a revision at ' . $archiveTitle->getPrefixedText() );
		}

		$content = $revision->getContent( Revision::RAW );
		if ( !$content instanceof WikitextContent ) {
			throw new FlowException( 'Expected wikitext content at: ' . $archiveTitle->getPrefixedText() );
		}
		$status = $page->doEditContent(
			$this->createArchiveCleanupRevisionContent( $content, $title ),
			'LQT to Flow conversion ',
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->user
		);

		if ( !$status->isGood() ) {
			throw new FlowException( 'Failed creating archive cleanup revision at ' . $archiveTitle->getPrefixedText() );
		}
	}

	/**
	 * @param WikitextContent $content
	 * @param Title $title
	 * @return WikitextContent
	 */
	protected function createArchiveCleanupRevisionContent( WikitextContent $content, Title $title ) {
		$now = new DateTime( "now", new DateTimeZone( 'GMT' ) );
		$arguments = implode( '|', array(
			'from=' . $title->getPrefixedText(),
			'date=' . $now->format( 'Y-m-d' ),
		) );

		$newWikitext = preg_replace(
			'/{{\s*#useliquidthreads:\s*1\s*}}/i',
			'',
			$content->getNativeData()
		);
		$newWikitext .= "\n\n{{Archive of LQT page converted to Flow|$arguments}}";

		return new WikitextContent( $newWikitext );
	}
}
