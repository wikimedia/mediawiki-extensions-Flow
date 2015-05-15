<?php

namespace Flow\Import\LiquidThreadsApi;

use DatabaseBase;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\ImportSourceStore;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Import\Postprocessor\LqtNotifications;
use Flow\Import\Postprocessor\LqtRedirector;
use Flow\NotificationController;
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
	 * @var DatabaseBase Master database for the current wiki
	 */
	protected $dbw;

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

	/**
	 * @var NotificationController
	 */
	protected $notificationController;

	public function __construct(
		DatabaseBase $dbw,
		ImportSourceStore $sourceStore,
		ApiBackend $api,
		UrlGenerator $urlGenerator,
		User $talkpageUser,
		NotificationController $notificationController
	) {
		$this->dbw = $dbw;
		$this->sourceStore = $sourceStore;
		$this->api = $api;
		$this->urlGenerator = $urlGenerator;
		$this->talkpageUser = $talkpageUser;
		$this->notificationController = $notificationController;
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
		$group = new ProcessorGroup;
		$group->add( new LqtRedirector( $this->urlGenerator, $this->talkpageUser ) );
		$group->add( new LqtNotifications( $this->notificationController, $this->dbw ) );

		return $group;
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldConvert( Title $sourceTitle ) {
		// The expensive part of this (user-override checking) is cached by LQT.
		return LqtDispatch::isLqtPage( $sourceTitle );
	}
}
