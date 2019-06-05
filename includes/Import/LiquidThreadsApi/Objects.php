<?php

namespace Flow\Import\LiquidThreadsApi;

use AppendIterator;
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
	 * @param int $pageId ID of the remote page
	 */
	public function __construct( $source, $pageId ) {
		$this->importSource = $source;
		$this->pageId = $pageId;
	}

	/**
	 * Gets the raw revisions, after filtering but before being converted to
	 * ImportRevision.
	 *
	 * @return array Page data with filtered revisions
	 */
	protected function getRevisionData() {
		$pageData = $this->importSource->getPageData( $this->pageId );
		// filter revisions without content (deleted)
		foreach ( $pageData['revisions'] as $key => $value ) {
			if ( isset( $value['texthidden'] ) ) {
				unset( $pageData['revisions'][$key] );
			}
		}
		// the iterators expect this to be a 0 indexed list
		$pageData['revisions'] = array_values( $pageData['revisions'] );
		return $pageData;
	}

	public function getRevisions() {
		$pageData = $this->getRevisionData();
		$scriptUser = $this->importSource->getScriptUser();
		return new RevisionIterator( $pageData, $this, function ( $data, $parent ) use ( $scriptUser ) {
			return new ImportRevision( $data, $parent, $scriptUser );
		} );
	}
}

class ImportPost extends PageRevisionedObject implements IImportPost {

	/**
	 * @var array
	 */
	protected $apiResponse;

	/**
	 * @param ImportSource $source
	 * @param array $apiResponse
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
	 * Gets the username (or IP) from the signature.
	 *
	 * @return string|null Returns username, IP, or null if none could be detected
	 */
	public function getSignatureUser() {
		$signatureText = $this->apiResponse['signature'];

		return self::extractUserFromSignature( $signatureText );
	}

	/**
	 * Gets the username (or IP) from the provided signature.
	 *
	 * @param string $signatureText
	 * @return string|null Returns username, IP, or null if none could be detected
	 */
	public static function extractUserFromSignature( $signatureText ) {
		$users = \EchoDiscussionParser::extractUsersFromLine( $signatureText );

		if ( count( $users ) > 0 ) {
			return $users[0];
		} else {
			return null;
		}
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

	public function getRevisions() {
		$authorUsername = $this->getAuthor();
		$signatureUsername = $this->getSignatureUser();
		if ( $signatureUsername !== null && $signatureUsername !== $authorUsername ) {
			$originalRevisionData = $this->getRevisionData();

			// This is not the same object as the last one in the original iterator,
			// but it should be fungible.
			$lastLqtRevision = new ImportRevision(
				end( $originalRevisionData['revisions'] ),
				$this,
				$this->importSource->getScriptUser()
			);
			$signatureRevision = $this->createSignatureClarificationRevision( $lastLqtRevision, $authorUsername, $signatureUsername );

			$originalRevisions = parent::getRevisions();
			$iterator = new AppendIterator();
			$iterator->append( $originalRevisions );
			$iterator->append( new ArrayIterator( [ $signatureRevision ] ) );
			return $iterator;
		} else {
			return parent::getRevisions();
		}
	}

	/**
	 * Creates revision clarifying signature difference
	 *
	 * @param IObjectRevision $lastRevision Last revision prior to the clarification revision
	 * @param string $authorUsername Author username
	 * @param string $signatureUsername Username extracted from signature
	 * @return ScriptedImportRevision Generated top import revision
	 */
	protected function createSignatureClarificationRevision( IObjectRevision $lastRevision, $authorUsername, $signatureUsername ) {
		$wikitextForLastRevision = $lastRevision->getText();
		$newWikitext = $wikitextForLastRevision;

		$templateName = wfMessage( 'flow-importer-lqt-different-author-signature-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', [
			"authorUser=$authorUsername",
			"signatureUser=$signatureUsername",
		] );

		$newWikitext .= "\n\n{{{$templateName}|$arguments}}";
		$clarificationRevision = new ScriptedImportRevision(
			$this,
			$this->importSource->getScriptUser(),
			$newWikitext,
			$lastRevision
		);
		return $clarificationRevision;
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
		return new ArrayIterator( [ $this ] );
	}

	public function getReplies() {
		$topPost = new ImportPost( $this->importSource, $this->apiResponse );
		return new ArrayIterator( [ $topPost ] );
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
	 * @return int
	 */
	protected function getSummaryId() {
		return $this->apiResponse['summaryid'];
	}

	/**
	 * This needs to have a different value than the same apiResponse in an ImportPost.
	 * The ImportPost version refers to the first response to the topic.
	 * @return string
	 */
	public function getObjectKey() {
		return 'topic' . $this->importSource->getObjectKey( 'thread_id', $this->apiResponse['id'] );
	}

	public function getLogType() {
		return "lqt-to-flow-topic";
	}

	public function getLogParameters() {
		return [
			'lqt_thread_id' => $this->apiResponse['id'],
			'lqt_orig_title' => $this->getTitle()->getPrefixedText(),
			'lqt_subject' => $this->getText(),
		];
	}

	public function getLqtThreadId() {
		return $this->apiResponse['id'];
	}
}

class ImportSummary extends PageRevisionedObject implements IImportSummary {
	/** @var ImportSource **/
	protected $source;

	/**
	 * @param array $apiResponse
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
	protected $parent;

	/** @var array **/
	protected $apiResponse;

	/**
	 * @var User Account used when the imported revision is by a suppressed user
	 */
	protected $scriptUser;

	/**
	 * Creates an ImportRevision based on a MW page revision
	 *
	 * @param array $apiResponse An element from api.query.revisions
	 * @param IImportObject $parentObject
	 * @param User $scriptUser Account used when the imported revision is by a suppressed user
	 */
	public function __construct( array $apiResponse, IImportObject $parentObject, User $scriptUser ) {
		$this->apiResponse = $apiResponse;
		$this->parent = $parentObject;
		$this->scriptUser = $scriptUser;
	}

	/**
	 * @return string
	 */
	public function getText() {
		$contentKey = 'content';

		$content = $this->apiResponse[$contentKey];

		if ( isset( $this->apiResponse['userhidden'] ) ) {
			$template = wfMessage( 'flow-importer-lqt-suppressed-user-template' )->inContentLanguage()->plain();

			$content .= "\n\n{{{$template}}}";
		}

		return $content;
	}

	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['timestamp'] );
	}

	public function getAuthor() {
		if ( isset( $this->apiResponse['userhidden'] ) ) {
			return $this->scriptUser->getName();
		} else {
			return $this->apiResponse['user'];
		}
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
		return new ArrayIterator( [ $topPost ] );
	}
}

class MovedImportPost extends ImportPost {
	public function getRevisions() {
		$scriptUser = $this->importSource->getScriptUser();
		$factory = function ( $data, $parent ) use ( $scriptUser ) {
			return new MovedImportRevision( $data, $parent, $scriptUser );
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
	 * @return string
	 * @throws ImportException
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
		$template = wfMessage( 'flow-importer-lqt-moved-thread-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', [
			'author=' . parent::getAuthor(),
			'date=' . MWTimestamp::getInstance( $this->apiResponse['timestamp'] )->timestamp->format( 'Y-m-d' ),
			'title=' . $target->getPrefixedText(),
		] );

		return "{{{$template}|$arguments}}";
	}
}

// Represents a revision the script makes on its own behalf, using a script user
class ScriptedImportRevision implements IObjectRevision {
	/** @var IImportObject **/
	protected $parent;

	/** @var User */
	protected $destinationScriptUser;

	/** @var string */
	protected $revisionText;

	/** @var string */
	protected $timestamp;

	/**
	 * Creates a ScriptedImportRevision with the given timestamp, given a script user
	 * and arbitrary text.
	 *
	 * @param IImportObject $parentObject Object this is a revision of
	 * @param User $destinationScriptUser User that performed this scripted edit
	 * @param string $revisionText Text of revision
	 * @param IObjectRevision $baseRevision Base revision, used only for timestamp generation
	 */
	public function __construct( IImportObject $parentObject, User $destinationScriptUser, $revisionText, $baseRevision ) {
		$this->parent = $parentObject;
		$this->destinationScriptUser = $destinationScriptUser;
		$this->revisionText = $revisionText;

		$baseTimestamp = wfTimestamp( TS_UNIX, $baseRevision->getTimestamp() );

		// Set a minute after.  If it uses $baseTimestamp again, there can be time
		// collisions.
		$this->timestamp = wfTimestamp( TS_UNIX, $baseTimestamp + 60 );
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

	public function __construct( ApiBackend $api, ImportSource $source, $title ) {
		$this->api = $api;
		$this->title = $title;
		$this->source = $source;
		$this->pageData = null;
	}

	public function getRevisions() {
		if ( $this->pageData === null ) {
			// Previous revisions of the header are preserved in the underlying wikitext
			// page history. Only the top revision is imported.
			$response = $this->api->retrieveTopRevisionByTitle( [ $this->title ] );
			$this->pageData = reset( $response );
		}

		$revisions = [];

		if ( isset( $this->pageData['revisions'] ) && count( $this->pageData['revisions'] ) > 0 ) {
			$lastLqtRevision = new ImportRevision(
				end( $this->pageData['revisions'] ),
				$this,
				$this->source->getScriptUser()
			);

			$titleObject = Title::newFromText( $this->title );
			$cleanupRevision = $this->createHeaderCleanupRevision( $lastLqtRevision, $titleObject );

			$revisions = [ $lastLqtRevision, $cleanupRevision ];
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
		$newWikitext = ConversionStrategy::removeLqtMagicWord( $wikitextForLastRevision );
		$templateName = wfMessage( 'flow-importer-lqt-converted-template' )->inContentLanguage()->plain();
		$arguments = implode( '|', [
			'archive=' . $archiveTitle->getPrefixedText(),
			'date=' . MWTimestamp::getInstance()->timestamp->format( 'Y-m-d' ),
		] );

		$newWikitext .= "\n\n{{{$templateName}|$arguments}}";

		$cleanupRevision = new ScriptedImportRevision(
			$this,
			$this->source->getScriptUser(),
			$newWikitext,
			$lastRevision
		);
		return $cleanupRevision;
	}

	public function getObjectKey() {
		return $this->source->getObjectKey( 'header_for', $this->title );
	}
}
