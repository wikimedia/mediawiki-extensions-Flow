<?php

namespace Flow\Import\Wikitext;

use ArrayIterator;
use Flow\Import\TemplateHelper;
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
use User;

/**
 * Imports the header of a wikitext talk page. Does not attempt to
 * parse out and return individual topics. See the wikitext
 * ConversionStrategy for more details.
 */
class ImportSource implements IImportSource {
	/** @var User User doing the conversion actions (e.g. initial description, wikitext
	 *    archive edit).  However, actions will be attributed to the original user if
	 *    applicable.
	 */
	protected $user;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var Parser|StubObject
	 */
	private $parser;

	/**
	 * @var string|null
	 */
	private $headerSuffix;

	/**
	 * @param Title $title
	 * @param Parser|StubObject $parser
	 * @param User $user User to take actions as
	 * @param string|null $headerSuffix
	 * @throws ImportException When $title is an external title
	 */
	public function __construct( Title $title, $parser, User $user, $headerSuffix = null ) {
		if ( $title->isExternal() ) {
			throw new ImportException( "Invalid non-local title: $title" );
		}
		$this->title = $title;
		$this->parser = $parser;
		$this->user = $user;
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
		$nativeContent = $revision->getContent()->getNativeData();
		$output = $this->parser->parse( $nativeContent, $this->title, new ParserOptions );
		$sections = $output->getSections();
		if ( $sections ) {
			$nativeContent = substr( $nativeContent, 0, $sections[0]['byteoffset'] );
		}

		$content = TemplateHelper::extractTemplates( $nativeContent, $this->title );

		$template = wfMessage( 'flow-importer-wt-converted-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', [
			'archive=' . $this->title->getPrefixedText(),
			'date=' . MWTimestamp::getInstance()->timestamp->format( 'Y-m-d' ),
		] );
		$content .= "\n\n{{{$template}|$arguments}}";

		if ( $this->headerSuffix && !empty( $this->headerSuffix ) ) {
			$content .= "\n\n{$this->headerSuffix}";
		}

		return new ImportHeader(
			[ new ObjectRevision(
				$content,
				wfTimestampNow(),
				$this->user->getName(),
				"wikitext-import:header-revision:{$revision->getId()}"
			) ],
			"wikitext-import:header:{$this->title->getPrefixedText()}"
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getTopics() {
		return new ArrayIterator( [] );
	}
}
