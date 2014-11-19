<?php

namespace Flow\Import\LiquidThreadsApi;

use DatabaseBase;
use DateTime;
use DateTimeZone;
use Flow\Import\IConversionStrategy;
use Flow\Import\ImportSourceStore;
use Title;
use WikitextContent;

/**
 * Converts LiquidThreads pages on a wiki to Flow. This converter is idempotent
 * when used with an appropriate ImportSourceStore, and may be run many times
 * without worry for duplicate imports.
 *
 * Pages with the LQT magic word will be moved to a subpage of their original location
 * named 'LQT Archive N' with N increasing starting at 1 looking for the first empty page.
 * On successfull import of an entire page the LQT magic word will be stripped from the
 * archive version of the page.
 */
class ConversionStrategy implements IConversionStrategy {
	/**
	 * @var DatabaseBase Slave database for the current wiki
	 */
	protected $dbr;

	/**
	 * @var ImportSourceStore
	 */
	protected $sourceStore;

	/**
	 * @var ApiBackend
	 */
	public $api;

	public function __construct(
		DatabaseBase $dbr,
		ImportSourceStore $sourceStore,
		ApiBackend $api
	) {
		$this->dbr = $dbr;
		$this->sourceStore = $sourceStore;
		$this->api = $api;
	}

	public function getSourceStore() {
		return $this->sourceStore;
	}

	public function getMoveComment( Title $from, Title $to ) {
		return "Conversion of LQT to Flow from: {$from->getPrefixedText()}";
	}

	public function getCleanupComment( Title $from, Title $to ) {
		return "LQT to Flow conversion";
	}

	public function isConversionFinished( Title $title, Title $movedFrom = null ) {
		// After successfull conversion we strip the LQT magic word
		if ( $this->isLiquidThreads( $title ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function createImportSource( Title $title ) {
		return new ImportSource( $this->api, $title->getPrefixedText() );
	}

	/**
	 * Flow does not support viewing the history of the wikitext pages it takes
	 * over, so those need to be moved out the way. This method decides that
	 * destination. The archived revisions include the headers displayed with
	 * lqt and potentially any pre-lqt wikitext talk page content.
	 *
	 * @param Title $source
	 * @return Title
	 */
	public function decideArchiveTitle( Title $source ) {
		return $this->findUsedByFormats( $source, array(
			'%s/LQT Archive %d',
		) );
	}

	/**
	 * @param WikitextContent $content
	 * @param Title $title
	 * @return WikitextContent
	 */
	public function createArchiveCleanupRevisionContent( WikitextContent $content, Title $title ) {
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
		$template = wfMessage( 'flow-importer-lqt-converted-template' )->inContentLanguage()->plain();
		$newWikitext .= "\n\n{{{$template}|$arguments}}";

		return new WikitextContent( $newWikitext );
	}

	/**
	 * @param Title $source
	 * @param string[] $formats
	 * @return Title
	 * @throws FlowException
	 */
	protected function findUsedByFormats( Title $source, array $formats ) {
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

	/**
	 * @param Title $title
	 * @return bool True if liquid threads is enabled on this title
	 */
	protected function isLiquidThreads( Title $title ) {
		return $this->pageHasProperty( $title, 'use-liquid-threads' );
	}

	/**
	 * @param Title $title
	 * @param string $property
	 * @return bool True if the page has the specified property
	 */
	protected function pageHasProperty( Title $title, $property ) {
		return (bool)$this->dbr->selectField(
			/* table */ array( 'page_props', 'page' ),
			/* select expr */ 1,
			/* cond */ array(
				'page_namespace' => $title->getNamespace(),
				'page_title' => $title->getDBkey(),
				'pp_page = page_id',
				'pp_propname' => $property,
			),
			__METHOD__
		);
	}
}
