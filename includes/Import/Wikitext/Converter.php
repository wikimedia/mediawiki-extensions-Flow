<?php

namespace Flow\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Exception\FlowException;
use Flow\Importer\IConversionStrategy;
use Flow\Importer\Wikitext\ImportSource;
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
 * a new flow board in place. We take either the entire page, or the page up
 * to the first section and put it into the title of the flow board. We
 * additionally add a localized template to both the archived wikitext page
 * and the new Flow board.
 *
 * It is plausible something with the EchoDiscussionParser could be worked up
 * to do an import of topics and posts. We know it wont work for everything,
 * but we don't know if it works for 90%, 99%, or 99.99% of topics. We know
 * for sure that it doesn't currently understand anything about editing an
 * existing comment.
 */
class ConversionStrategy implements IConversionStrategy {
	/**
	 * @var ImportSourceStore
	 */
	protected $sourceStore;

	/**
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @param Parser|StubObject $parser
	 * @param ImportSourceStore $sourceStore
	 */
	public function __construct( $parser, ImportSourceStore $sourceStore ) {
		$this->parser = $parser;
		$this->sourceStore = $sourceStore;
	}

	public function getSourceStore() {
		return $this->sourceStore;
	}

	public function getMoveComment( Title $from, Title $to ) {
		return "";
	}

	public function getCleanupComment( Title $from, Title $to ) {
		return "";
	}

	public function isConversionFinished( Title $title, Title $movedFrom = null ) {
		if ( $movedFrom ) {
			// no good way to pick up where we left off
			return true;
		} else {
			return false;
		}
	}

	public function createImportSource( Title $title ) {
		return new ImportSource( $title, $this->parser );
	}

	public function decideArchiveTitle( Title $source ) {
		return $this->findUsedByForms( $source, array(
			'%s/Archive %d',
			'%s/Archive%d',
			'%s/archive %d',
			'%s/archive%d',
		) );
	}

	public function createArchiveCleanupRevisionContent( WikitextContent $content, Title $title ) {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = implode( '|', array(
			'from=' . $title->getPrefixedText(),
			'date=' . $now->format( 'Y-m-d' ),
		) );

		$newWikitext = $content->getNativeData() . "\n\n{{Archive of wikitext talk page converted to Flow|$arguments}}";

		return new WikitextContent( $newWikitext );
	}
}
