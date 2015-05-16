<?php

namespace Flow\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\ImportSourceStore;
use Parser;
use Psr\Log\LoggerInterface;
use StubObject;
use Title;
use WikitextContent;

/**
 * Does not really convert. Archives wikitext pages out of the way and puts
 * a new flow board in place. We take either the entire page, or the page up
 * to the first section and put it into the header of the flow board. We
 * additionally edit both the flow header and the archived page to include
 * a localized template containing the reciprocal title and the conversion
 * date in GMT.
 *
 * It is plausible something with the EchoDiscussionParser could be worked up
 * to do an import of topics and posts. We know it wont work for everything,
 * but we don't know if it works for 90%, 99%, or 99.99% of topics. We know
 * for sure that it does not currently understand anything about editing an
 * existing comment.
 */
class ConversionStrategy implements IConversionStrategy {
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var ImportSourceStore
	 */
	protected $sourceStore;

	/**
	 * @var Parser|StubObject
	 */
	protected $parser;

	/**
	 * @var array $archiveTitleSuggestions
	 */
	protected $archiveTitleSuggestions;

	/**
	 * @var string $headerSuffix
	 */
	protected $headerSuffix;

	/**
	 * @param Parser|StubObject $parser
	 * @param ImportSourceStore $sourceStore
	 */
	public function __construct(
		$parser,
		ImportSourceStore $sourceStore,
		LoggerInterface $logger,
		$preferredArchiveTitle = null,
		$headerSuffix = null
	) {
		$this->parser = $parser;
		$this->sourceStore = $sourceStore;
		$this->logger = $logger;
		$this->headerSuffix = $headerSuffix;

		if ( isset( $preferredArchiveTitle ) && !empty( $preferredArchiveTitle ) ) {
			$this->archiveTitleSuggestions = array( $preferredArchiveTitle );
		} else {
			$this->archiveTitleSuggestions = array(
				'%s/Archive %d',
				'%s/Archive%d',
				'%s/archive %d',
				'%s/archive%d',
			);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSourceStore() {
		return $this->sourceStore;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMoveComment( Title $from, Title $to ) {
		return wfMessage( 'flow-talk-conversion-move-reason', $from->getPrefixedText() )->plain();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCleanupComment( Title $from, Title $to ) {
		return wfMessage( 'flow-talk-conversion-archive-edit-reason' )->plain();
	}

	/**
	 * @{inheritDoc}
	 */
	public function isConversionFinished( Title $title, Title $movedFrom = null ) {
		if ( $movedFrom ) {
			// no good way to pick up where we left off
			return true;
		} else {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function createImportSource( Title $title ) {
		return new ImportSource( $title, $this->parser, $this->headerSuffix );
	}

	/**
	 * {@inheritDoc}
	 */
	public function decideArchiveTitle( Title $source ) {
		return Converter::decideArchiveTitle( $source, $this->archiveTitleSuggestions );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPostprocessor() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createArchiveCleanupRevisionContent( WikitextContent $content, Title $title ) {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = implode( '|', array(
			'from=' . $title->getPrefixedText(),
			'date=' . $now->format( 'Y-m-d' ),
		) );

		$template = wfMessage( 'flow-importer-wt-converted-archive-template' )->inContentLanguage()->plain();
		$newWikitext = "{{{$template}|$arguments}}" . "\n\n" . $content->getNativeData();

		return new WikitextContent( $newWikitext );
	}

	// Public only for unit testing
	/**
	 * Checks whether it meets the applicable subpage rules.  Meant to be overriden by
	 * subclasses that do not have the same requirements
	 *
	 * @param Title $sourceTitle Title to check
	 * @return bool Whether it meets the applicable subpage requirements
	 */
	public function meetsSubpageRequirements( $sourceTitle ) {
		// Don't allow conversion of sub pages unless it is
		// a talk page with matching subject page. For example
		// we will convert User_talk:Foo/bar only if User:Foo/bar
		// exists, and we will never convert User:Baz/bang.
		if ( $sourceTitle->isSubPage() && ( !$sourceTitle->isTalkPage() || !$sourceTitle->getSubjectPage()->exists() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldConvert( Title $sourceTitle ) {
		// If we have LiquidThreads filter out any pages with that enabled.  They should
		// be converted separately.
		if ( class_exists( 'LqtDispatch' ) ) {
			if ( \LqtDispatch::isLqtPage( $sourceTitle ) ) {
				$this->logger->info( "Skipping LQT enabled page, conversion must be done with convertLqt.php or convertLqtPageOnLocalWiki.php: $sourceTitle" );
				return false;
			}
		}

		if ( !$this->meetsSubpageRequirements( $sourceTitle ) ) {
			return false;
		}

		return true;
	}
}
