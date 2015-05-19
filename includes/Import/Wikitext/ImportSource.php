<?php

namespace Flow\Import\Wikitext;

use ArrayIterator;
use FlowHooks;
use Flow\Import\ImportException;
use Flow\Import\Plain\ImportHeader;
use Flow\Import\Plain\ObjectRevision;
use Flow\Import\IImportSource;
use MWTimestamp;
use Parser;
use ParserOptions;
use Revision;
use StubObject;
use Title;

/**
 * Imports the header of a wikitext talk page. Does not attempt to
 * parse out and return individual topics. See the wikitext
 * ConversionStrategy for more details.
 */
class ImportSource implements IImportSource {

	/**
	 * @param Title $title
	 * @param Parser|StubObject $parser
	 * @param string $headerSuffix
	 * @throws ImportException When $title is an external title
	 */
	public function __construct( Title $title, $parser, $headerSuffix = null ) {
		if ( $title->isExternal() ) {
			throw new ImportException( "Invalid non-local title: $title" );
		}
		$this->title = $title;
		$this->parser = $parser;
		$this->headerSuffix = $headerSuffix;
	}

	/**
	 * Converts the existing wikitext talk page into a flow board header.
	 * If sections exist the header only receives the content up to the
	 * first section. Appends a template to the output indicating conversion
	 * occurred parameterized with the page the source lives at and the date
	 * of conversion in GMT.
	 *
	 * @return ImportHeader
	 * @throws ImportException When source header revision can not be loaded
	 */
	public function getHeader() {
		$revision = Revision::newFromTitle( $this->title, /* $id= */ 0, Revision::READ_LATEST );
		if ( !$revision ) {
			throw new ImportException( "Failed to load revision for title: {$this->title->getPrefixedText()}" );
		}

		// If sections exist only take the content from the top of the page
		// to the first section.
		$content = $revision->getContent()->getNativeData();
		$output = $this->parser->parse( $content, $this->title, new ParserOptions );
		$sections = $output->getSections();
		if ( $sections ) {
			$content = substr( $content, 0, $sections[0]['byteoffset'] );
		}

		// Only extract templates to copy to Flow description.
		// This regular expression based way of extracting the template is
		// far from ideal, but it should probably be good enough and
		// alternatives will be hacky/much more work for no apparent gain.
		preg_match_all( '/\{\{.+?\}\}/', $content, $matches, PREG_SET_ORDER );
		$content = '';
		foreach ( $matches as $match ) {
			$content .= $match[0] . "\n";
		}

		$template = wfMessage( 'flow-importer-wt-converted-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', array(
			'archive=' . $this->title->getPrefixedText(),
			'date=' . MWTimestamp::getInstance()->timestamp->format( 'Y-m-d' ),
		) );
		$content .= "\n\n{{{$template}|$arguments}}";

		if ( $this->headerSuffix && !empty( $this->headerSuffix ) ) {
			$content .= "\n\n{$this->headerSuffix}";
		}

		return new ImportHeader(
			array( new ObjectRevision(
				$content,
				wfTimestampNow(),
				FlowHooks::getOccupationController()->getTalkpageManager()->getName(),
				"wikitext-import:header-revision:{$revision->getId()}"
			) ),
			"wikitext-import:header:{$this->title->getPrefixedText()}"
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTopics() {
		return new ArrayIterator( array() );
	}
}

