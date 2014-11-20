<?php

namespace Flow\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\ImportSourceStore;
use Parser;
use StubObject;
use Title;
use WikitextContent;

/**
 * Does not really convert. Archives wikitext pages out of the way and puts
 * a new flow board in place. We take either the entire page, or the page up
 * to the first section and put it into the header of the flow board. We
 * additionally edit both the flow header and the archived pge to include a
 * localized template containing the reciprocal title and the GMT date.
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
		return wfMessage( 'flow-talk-conversion-move-reason' )->plain();
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
		return new ImportSource( $title, $this->parser );
	}

	/**
	 * {@inheritDoc}
	 */
	public function decideArchiveTitle( Title $source ) {
		return Converter::decideArchiveTitle( $source, array(
			'%s/Archive %d',
			'%s/Archive%d',
			'%s/archive %d',
			'%s/archive%d',
		) );
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

		$template = wfMessage( 'flow-imported-wt-converted-archive-template' )->inContentLanguage()->plain();
		$newWikitext = $content->getNativeData() . "\n\n{{{$template}|$arguments}}";

		return new WikitextContent( $newWikitext );
	}
}
