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

abstract class PageRevisionedObject implements IRevisionableObject {
	/** @var int **/
	protected $pageId;

	/**
	 * @var ImportSource
	 */
	protected $importSource;

	/**
	 * @param ImportSource $source
	 * @param int          $pageId ID of the remote page
	 */
	function __construct( $source, $pageId ) {
		$this->importSource = $source;
		$this->pageId = $pageId;
	}

	public function getRevisions() {
		$pageData = $this->importSource->getPageData( $this->pageId );
		return new RevisionIterator( $pageData, $this );
	}
}

class ImportPost extends PageRevisionedObject implements IImportPost {

	/**
	 * @var array
	 */
	protected $apiResponse;

	/**
	 * @param ImportSource $source
	 * @param array        $apiResponse
	 */
	public function __construct( ImportSource $source, array $apiResponse ) {
		parent::__construct( $source, $apiResponse['rootid'] );
		$this->apiResponse = $apiResponse;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->apiResponse['author']['name'];
	}

	/**
	 * @return string|false
	 */
	public function getCreatedTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['created'] );
	}

	/**
	 * @return string|false
	 */
	public function getModifiedTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['modified'] );
	}

	/**
	 * @return string
	 */
	public function getText() {
		$pageData = $this->importSource->getPageData( $this->apiResponse['rootid'] );
		$revision = $pageData['revisions'][0];
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$contentKey = isset( $revision[ApiResult::META_CONTENT] )
				? $revision[ApiResult::META_CONTENT]
				: '*';
		} else {
			$contentKey = '*';
		}

		return $revision[$contentKey];
	}

	public function getTitle() {
		$pageData = $this->importSource->getPageData( $this->apiResponse['rootid'] );

		return Title::newFromText( $pageData['title'] );
	}

	/**
	 * @return Iterator<IImportPost>
	 */
	public function getReplies() {
		return new ReplyIterator( $this );
	}

	/**
	 * @return array
	 */
	public function getApiResponse() {
		return $this->apiResponse;
	}

	/**
	 * @return ImportSource
	 */
	public function getSource() {
		return $this->importSource;
	}

	public function getObjectKey() {
		return $this->importSource->getObjectKey( 'thread_id', $this->apiResponse['id'] );
	}
}

/**
 * This is a bit of a weird model, acting as a revision of itself.
 */
class ImportTopic extends ImportPost implements IImportTopic, IObjectRevision {
	/**
	 * @return string
	 */
	public function getText() {
		return $this->apiResponse['subject'];
	}

	public function getAuthor() {
		return $this->apiResponse['author']['name'];
	}

	public function getRevisions() {
		// we only have access to a single revision of the topic
		return new ArrayIterator( array( $this ) );
	}

	public function getReplies() {
		$topPost = new ImportPost( $this->importSource, $this->apiResponse );
		return new ArrayIterator( array( $topPost ) );
	}

	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['created'] );
	}

	/**
	 * @return IImportSummary|null
	 */
	public function getTopicSummary() {
		$id = $this->getSummaryId();
		if ( $id > 0 ) {
			$data = $this->importSource->getPageData( $id );
			if ( isset( $data['revisions'][0] ) ) {
				return new ImportSummary( $data, $this->importSource );
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	/**
	 * @return integer
	 */
	protected function getSummaryId() {
		return $this->apiResponse['summaryid'];
	}

	/**
	 * This needs to have a different value than the same apiResponse in an ImportPost.
	 * The ImportPost version refers to the first response to the topic.
	 */
	public function getObjectKey() {
		return 'topic' . $this->importSource->getObjectKey( 'thread_id', $this->apiResponse['id'] );
	}

	public function getLogType() {
		return "lqt-to-flow-topic";
	}

	public function getLogParameters() {
		return array(
			'lqt_thread_id' => $this->apiResponse['id'],
			'lqt_orig_title' => $this->getTitle()->getPrefixedText(),
			'lqt_subject' => $this->getText(),
		);
	}
}

class ImportSummary extends PageRevisionedObject implements IImportSummary {
	/** @var ImportSource **/
	protected $source;

	/**
	 * @param array        $apiResponse
	 * @param ImportSource $source
	 * @throws ImportException
	 */
	public function __construct( array $apiResponse, ImportSource $source ) {
		parent::__construct( $source, $apiResponse['pageid'] );
	}

	public function getObjectKey() {
		return $this->importSource->getObjectKey( 'summary_id', $this->pageId );
	}
}

class ImportRevision implements IObjectRevision {
	/** @var IImportObject **/
	protected $parentObject;

	/** @var array **/
	protected $apiResponse;

	/**
	 * Creates an ImportRevision based on a MW page revision
	 *
	 * @param array         $apiResponse  An element from api.query.revisions
	 * @param IImportObject $parentObject
	 */
	function __construct( array $apiResponse, IImportObject $parentObject ) {
		$this->apiResponse = $apiResponse;
		$this->parent = $parentObject;
	}

	/**
	 * @return string
	 */
	public function getText() {
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$contentKey = isset( $this->apiResponse[ApiResult::META_CONTENT] )
				? $this->apiResponse[ApiResult::META_CONTENT]
				: '*';
		} else {
			$contentKey = '*';
		}

		return $this->apiResponse[$contentKey];
	}

	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['timestamp'] );
	}

	public function getAuthor() {
		return $this->apiResponse['user'];
	}

	public function getObjectKey() {
		return $this->parent->getObjectKey() . ':rev:' . $this->apiResponse['revid'];
	}
}

// The Moved* series of topics handle the LQT move stubs.  They need to
// have their revision content rewriten from #REDIRECT to a template that
// has visible output like lqt generated per-request.
class MovedImportTopic extends ImportTopic {
	public function getReplies() {
		$topPost = new MovedImportPost( $this->importSource, $this->apiResponse );
		return new ArrayIterator( array( $topPost ) );
	}
}

class MovedImportPost extends ImportPost {
	public function getRevisions() {
		$factory = function( $data, $parent ) {
			return new MovedImportRevision( $data, $parent );
		};
		$pageData = $this->importSource->getPageData( $this->pageId );
		return new RevisionIterator( $pageData, $this, $factory );
	}
}

class MovedImportRevision extends ImportRevision {
	/**
	 * Rewrites the '#REDIRECT [[...]]' of an autogenerated lqt moved
	 * thread stub into a template.  While we don't re-write the link
	 * here, after importing the referenced thread LqtRedirector will
	 * make that Thread page a redirect to the Flow topic, essentially
	 * making these links still work.
	 */
	public function getText() {
		$text = parent::getText();
		$content = \ContentHandler::makeContent( $text, null, CONTENT_MODEL_WIKITEXT );
		$target = $content->getRedirectTarget();
		if ( !$target ) {
			throw new ImportException( "Could not detect redirect within: $text" );
		}

		// To get the new talk page that this belongs to we would need to query the api
		// for the new topic, for now not bothering.
		$template = wfMessage( 'flow-importer-lqt-moved-thread' )->inContentLanguage()->plain();
		$arguments = implode( '|', array(
			'author=' . parent::getAuthor(),
			'date=' . MWTimestamp::getInstance( $this->apiResponse['timestamp'] )->timestamp->format( 'Y-m-d' ),
			'title=' . $target->getPrefixedText(),
		) );

		return "{{{$template}|$arguments}}";
	}
}

// Represents a revision the script makes on its own behalf, using a script user
class ScriptedImportRevision implements IObjectRevision {
	/** @var IImportObject **/
	protected $parentObject;

	/** @var User */
	protected $destinationScriptUser;

	/** @var string */
	protected $revisionText;

	/** @var string */
	protected $timestamp;

	/**
	 * Creates a ScriptedImportRevision with the current timestamp, given a script user
	 * and arbitrary text.
	 *
	 * @param IImportObject $parentObject Object this is a revision of
	 * @param User $destinationScriptUser User that performed this scripted edit
	 * @param string $revisionText Text of revision
	 */
	function __construct( IImportObject $parentObject, User $destinationScriptUser, $revisionText ) {
		$this->parent = $parentObject;
		$this->destinationScriptUser = $destinationScriptUser;
		$this->revisionText = $revisionText;
		$this->timestamp = wfTimestampNow();
	}

	public function getText() {
		return $this->revisionText;
	}

	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getAuthor() {
		return $this->destinationScriptUser->getName();
	}

	// XXX: This is called but never used, but if it were, including getText and getAuthor in
	// the key might not be desirable, because we don't necessarily want to re-import
	// the revision when these change.
	public function getObjectKey() {
		return $this->parent->getObjectKey() . ':rev:scripted:' . md5( $this->getText() . $this->getAuthor() );
	}
}

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
