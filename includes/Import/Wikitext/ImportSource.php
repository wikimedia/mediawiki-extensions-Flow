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

	public function __construct( Title $title ) {
		if ( !$title->isLocal() ) {
			throw new \MWException( "Invalid non-local title: {$title->getPrefixedText()}" );
		}
		$this->title = $title;
	}

	public function getHeader() {
		global $wgParser;

		$rev = $this->getHeaderRevision();
		if ( $rev === null ) {
			return null;
		}

		return new ImportHeader( array(
			$rev,
			$this->generateNextRevision( $rev ),
		) );
	}

	/**
	 * @return IObjectRevision|null
	 */
	protected function getHeaderRevision() {
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
		$output = $parser->parse( $content, $this->title, $options );
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

		return new ObjectRevision(
			$content,
			wfTimestampNow(),
			FlowHooks::getOccupationController()->getTalkpageManager()
		);
	}

	public function getTopics() {
		return new ArrayIterator( array() );
	}
}

