<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportHeader;
use Flow\Import\IImportObject;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
use Flow\Import\IObjectRevision;
use Flow\Import\IRevisionableObject;
use Iterator;
use MWTimestamp;
use Title;
use User;

class ImportHeader extends PageRevisionedObject implements IImportHeader {
	/** @var ApiBackend **/
	protected $api;
	/** @var string **/
	protected $title;
	/** @var array **/
	protected $pageData;
	/** @var ImportSource **/
	protected $source;
	/**
	 *  User used for script-originated actions, such as cleanup edits.
	 *  Does not apply to actual posts, which retain their original users.
	 *
	 *  @var User
	 */
	protected $destinationScriptUser;

	public function __construct( ApiBackend $api, ImportSource $source, $title, User $destinationScriptUser ) {
		$this->api = $api;
		$this->title = $title;
		$this->source = $source;
		$this->pageData = null;
		$this->destinationScriptUser = $destinationScriptUser;
	}

	public function getRevisions() {
		if ( $this->pageData === null ) {
			// Previous revisions of the header are preserved in the underlying wikitext
			// page history. Only the top revision is imported.
			$response = $this->api->retrieveTopRevisionByTitle( array( $this->title ) );
			$this->pageData = reset( $response );
		}

		$revisions = array();

		if ( isset( $this->pageData['revisions'] ) && count( $this->pageData['revisions'] ) > 0 ) {
			$lastLqtRevision = new ImportRevision( end( $this->pageData['revisions'] ), $this );

			$titleObject = Title::newFromText( $this->title );
			$cleanupRevision = $this->createHeaderCleanupRevision( $lastLqtRevision, $titleObject );

			$revisions = array( $lastLqtRevision, $cleanupRevision );
		}

		return new ArrayIterator( $revisions );
	}

	/**
	 * @param IObjectRevision $lastRevision last imported header revision
	 * @param Title $archiveTitle archive page title associated with header
	 * @return IObjectRevision generated revision for cleanup edit
	 */
	protected function createHeaderCleanupRevision( IObjectRevision $lastRevision, Title $archiveTitle ) {
		$wikitextForLastRevision = $lastRevision->getText();
		// This is will remove all instances, without attempting to check if it's in
		// nowiki, etc.  It also ignores case and spaces in places where it doesn't
		// matter.
		$newWikitext = preg_replace(
			'/{{\s*#useliquidthreads:\s*1\s*}}/i',
			'',
			$wikitextForLastRevision
		);
		$templateName = wfMessage( 'flow-importer-lqt-converted-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', array(
			'archive=' . $archiveTitle->getPrefixedText(),
			'date=' . MWTimestamp::getInstance()->timestamp->format( 'Y-m-d' ),
		) );

		$newWikitext .= "\n\n{{{$templateName}|$arguments}}";
		$cleanupRevision = new ScriptedImportRevision(
			$this,
			$this->destinationScriptUser,
			$newWikitext,
			$lastRevision->getTimestamp()
		);
		return $cleanupRevision;
	}

	public function getObjectKey() {
		return $this->source->getObjectKey( 'header_for', $this->title );
	}
}

