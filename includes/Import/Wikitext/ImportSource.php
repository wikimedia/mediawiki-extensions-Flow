<?php

namespace Flow\Import\Wikitext;

use DateTime;
use DateTimeZone;
use FlowHooks;
use Flow\Import\Basic\ImportHeader;
use Flow\Import\Basic\ObjectRevision;
use ParserOptions;
use Title;

class ImportSource implements IImportSource {

	/**
	 * @param Title $title
	 * @param Parser|StubObject $parser
	 */
	public function __construct( Title $title, $parser ) {
		if ( !$title->isLocal() ) {
			throw new \MWException( "Invalid non-local title: {$title->getPrefixedText()}" );
		}
		$this->title = $title;
		$this->parser = $parser;
	}

	/**
	 * Converts the existing wikitext talk page into a flow board header.
	 * If sections exist the header only recieves the content up to the
	 * first section.
	 *
	 * @return ImportHeader|null
	 */
	public function getHeader() {
		$revision = Revision::newFromTitle( $this->title );
		if ( !$revision ) {
			return null;
		}

		$options = new ParserOptions;
		$options->setTidy( true );
		$options->setEditSection( false );
		$content = $revision->getContent();

		// If sections exist only take the content from the top of the page
		// to the first section.
		$output = $this->parser->parse( $content, $this->title, $options );
		$sections = $output->getSections();
		if ( $sections ) {
			$content = substr( $content, 0, $sections[0]['byteoffset'] );
		}

		$templateName = wfMessage( 'flow-importer-wt-converted-template' )->inContentLanguage()->plain();
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$arguments = implode( '|', array(
			'from=' . $this->title->getPrefixedText(),
			'date=' . $now->format( 'Y-m-d' ),
		) );
		$content .= "\n\n{{{$templateName}|$arguments}}";

		return new ImportHeader(
			array( new ObjectRevision(
				$content,
				wfTimestampNow(),
				FlowHooks::getOccupationController()->getTalkpageManager(),
				"wikitext-import:header:{$revision->getId()}"
			) ),
			"wikitext-import:header:{$this->title->getPrefixedText()}"
		);
	}

	public function getTopics() {
		return new ArrayIterator( array() );
	}
}

