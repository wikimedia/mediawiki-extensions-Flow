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
