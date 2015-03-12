<?php

namespace Flow\Import\LiquidThreadsApi;

use DatabaseBase;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\ImportSourceStore;
use Flow\Import\Postprocessor\LqtRedirector;
use Flow\UrlGenerator;
use LqtDispatch;
use MWTimestamp;
use Title;
use User;
use WikitextContent;

/**
 * Converts LiquidThreads pages on a wiki to Flow. This converter is idempotent
 * when used with an appropriate ImportSourceStore, and may be run many times
 * without worry for duplicate imports.
 *
 * Pages with the LQT magic word will be moved to a subpage of their original location
 * named 'LQT Archive N' with N increasing starting at 1 looking for the first empty page.
 * On successful import of an entire page the LQT magic word will be stripped from the
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

	/**
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var User
	 */
	protected $talkpageUser;

	public function __construct(
		DatabaseBase $dbr,
		ImportSourceStore $sourceStore,
		ApiBackend $api,
		UrlGenerator $urlGenerator,
		User $talkpageUser
	) {
		$this->dbr = $dbr;
		$this->sourceStore = $sourceStore;
		$this->api = $api;
		$this->urlGenerator = $urlGenerator;
		$this->talkpageUser = $talkpageUser;
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
		// After successful conversion we strip the LQT magic word
		if ( LqtDispatch::isLqtPage( $title ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function createImportSource( Title $title ) {
		return new ImportSource( $this->api, $title->getPrefixedText(), $this->talkpageUser );
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
		return Converter::decideArchiveTitle( $source, array(
			'%s/LQT Archive %d',
		) );
	}

	/**
	 * @param WikitextContent $content
	 * @param Title $title
	 * @return WikitextContent
	 */
	public function createArchiveCleanupRevisionContent( WikitextContent $content, Title $title ) {
		$arguments = implode( '|', array(
			'from=' . $title->getPrefixedText(),
			'date=' . MWTimestamp::getInstance()->timestamp->format( 'Y-m-d' ),
		) );

		$newWikitext = preg_replace(
			'/{{\s*#useliquidthreads:\s*1\s*}}/i',
			'',
			$content->getNativeData()
		);
		$template = wfMessage( 'flow-importer-lqt-converted-archive-template' )->inContentLanguage()->plain();
		$newWikitext .= "\n\n{{{$template}|$arguments}}";

		return new WikitextContent( $newWikitext );
	}

	public function getPostprocessor() {
		$redirector = new LqtRedirector( $this->urlGenerator, $this->talkpageUser );
		return $redirector;
	}
}
