<?php

namespace Flow\Import\LiquidThreadsApi;

use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use User;

class ImportThread {
	protected $apiResponse;
	protected $source;

	public function __construct( array $apiResponse ) {
		$this->apiResponse = $apiResponse;
	}

	public function getCreatedTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['created'] );
	}

	public function getModifiedTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['modified'] );
	}

	public function getAuthor() {
		return User::newFromName( $this->apiResponse['author']['name'], false );
	}

	public function getThreadId() {
		return $this->apiResponse['id'];
	}

	public function getReplies() {
		return $this->apiResponse['replies'];
	}

	public function getApiResponse() {
		return $this->apiResponse;
	}
}

class ImportTopic extends ImportThread implements IImportTopic {
	public function getSubject() {
		return $this->apiResponse['subject'];
	}

	public function getCreator() {
		return $this->getAuthor();
	}

	public function getSummaryId() {
		return $this->apiResponse['summaryid'];
	}

	public function hasSummary() {
		return $this->getSummaryId() > 0;
	}
}

class ImportPost extends ImportThread implements IImportPost {
	protected $pageData, $apiResponse;

	public function __construct( array $apiResponse, CachedPageData $pageData ) {
		$this->apiResponse = $apiResponse;
		$this->pageData = $pageData;
	}

	public function getText() {
		$pageData = $this->pageData->get( $this->apiResponse['rootid'] );
		$firstRevision = reset( $pageData['revisions'] );
		return $firstRevision['*'];
	}
}

class ImportSummary implements IImportSummary {
	protected $pageid, $pageData;

	public function __construct( $pageid, $pageData ) {
		$this->pageid = $pageid;
		$this->pageData = $pageData;
	}

	public function getText() {
		$data = $this->getPageData();
		return $data['revisions']['*'];
	}

	public function getUser() {
		$pageData = $this->getPageData();
		return User::newFromName( $pageData['user'], false );
	}

	protected function getPageData() {
		return $this->pageData->get( $this->pageid );
	}
}
