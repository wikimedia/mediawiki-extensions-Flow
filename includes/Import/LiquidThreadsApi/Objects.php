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

		return $pageData['revisions'][0]['*'];
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
		return wfTimestamp( TS_MW, $this->apiResponse['modified'] );
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
		return $this->apiResponse['*'];
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
			$response = $this->api->retrievePageDataByTitle( array( $this->title ) );
			$this->pageData = reset( $response );
		}

		return new RevisionIterator( $this->pageData, $this );
	}

	public function getObjectKey() {
		return $this->source->getObjectKey( 'header_for', $this->title );
	}
}
