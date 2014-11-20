<?php

namespace Flow\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Exception\FlowException;
use MovePage;
use MWExceptionHandler;
use Psr\Log\LoggerInterface;
use Revision;
use Title;
use User;
use WikiPage;
use WikitextContent;

/**
 * Does not really convert. Archives wikitext pages out of the way and puts
 * a new flow board in place. No flow revision is created, after conversion
 * the namespace must be configured with flow-board as the default content
 * model.
 *
 * It is plausible something with the EchoDiscussionParser could be worked up
 * to do an import. We know it wont work for everything, but we don't know if
 * it works for 90%, 99%, or 99.99% of topics.
 */
class Converter {
	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	public function __construct( User $user, LoggerInterface $logger ) {
		$this->user = $user;
		$this->logger = $logger;
	}

	/**
	 * @param Traversable<Title> $titles
	 */
	public function convert( $titles ) {
		/** @var Title $title */
		foreach ( $titles as $title ) {
			if ( $title->isSubpage() ) {
				continue;
			}
			if ( $title->getContentModel() !== 'wikitext' ) {
				continue;
			}
			// @todo check for lqt

			$titleText = $title->getPrefixedText();
			try {
				$archiveTitle = $this->decideArchiveTitle( $title );
				$archiveTitleText = $archiveTitle->getPrefixedText();
				$this->logger->info( "Archiving page from $titleText to $archiveTitleText" );
				$this->movePage( $title, $archiveTitle );
				$this->createArchiveRevision( $title, $archiveTitle );
			} catch ( \Exception $e ) {
				MWExceptionHandler::logException( $e );
				$this->logger->error( "Exception while importing: $titleText" );
				$this->logger->error( (string)$e );
			}
		}
	}

	protected function movePage( Title $from, Title $to ) {
		$mp = new MovePage( $from, $to );
		$valid = $mp->isValidMove();
		if ( !$valid->isOK() ) {
			throw new FlowException( "It is not valid to move {$from->getPrefixedText()} to {$to->getPrefixedText()}" );
		}

		$status = $mp->move(
			/* user */ $this->user,
			/* reason */ "Conversion of wikitext talk to Flow from: {$from->getPrefixedText()}",
			/* create redirect */ false
		);

		if ( !$status->isGood() ) {
			throw new FlowException( "Failed moving {$from->getPrefixedText()} to {$to->getPrefixedText()}" );
		}
	}

	protected function decideArchiveTitle( Title $source ) {
		// @todo i18n.  Would bes
		$formats = array(
			'%s/Archive %d',
			'%s/Archive%d',
			'%s/archive %d',
			'%s/archive%d',
		);

		$format = false;
		$n = 1;
		$text = $source->getPrefixedText();
		foreach ( $formats as $potential ) {
			$title = Title::newFromText( sprintf( $potential, $text, $n ) );
			if ( $title && $title->exists() ) {
				$format = $potential;
				break;
			}
		}
		if ( $format === false ) {
			// assumes this creates a valid title
			return Title::newFromText( sprintf( $formats[0], $text, $n ) );
		}
		for ( ++$n; $n < 20; ++$n ) {
			$title = Title::newFromText( sprintf( $format, $n ) );
			if ( $title && !$title->exists() ) {
				return $title;
			}
		}

		throw new FlowException( "All titles 1 through 20 exist for format: $format" );
	}

	protected function createArchiveRevision( Title $title, Title $archiveTitle ) {
		$page = WikiPage::factory( $archiveTitle );
		$revision = $page->getRevision();
		if ( $revision === null ) {
			throw new FlowException( "Expected a revision at {$archiveTitle->getPrefixedText()}." );
		}

		$content = $revision->getContent( Revision::RAW );
		if ( !$content instanceof WikitextContent ) {
			throw new FlowException( "Expected wikitext content at {$archiveTitle->getPrefixedText()}." );
		}
		$status = $page->doEditContent(
			$this->createArchiveRevisionContent( $content, $title ),
			'Wikitext talk to Flow conversion',
			EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
			false,
			$this->user
		);

		if ( !$status->isGood() ) {
			throw new FlowException( "Failed creating archive revision at {$archiveTitle->getPrefixedText()}" );
		}
	}

	protected function createArchiveRevisionContent( WikitextContent $content, Title $title ) {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = implode( '|', array(
			'from=' . $title->getPrefixedText(),
			'date=' . $now->format( 'Y-m-d' ),
		) );

		$newWikitext = $content->getNativeData() . "\n\n{{Archive of wikitext talk page converted to Flow|$arguments}}";

		return new WikitextContent( $newWikitext );
	}
}
