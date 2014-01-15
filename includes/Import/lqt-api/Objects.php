<?php

namespace Flow\Import;

use User;

class LiquidThreadsApiThread {
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

class LiquidThreadsApiImportTopic extends LiquidThreadsApiThread implements ImportTopic {
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

class LiquidThreadsApiImportPost extends LiquidThreadsApiThread implements ImportPost {
	protected $pageData, $apiResponse;

	public function __construct( array $apiResponse, CachedPageData $pageData ) {
		$this->apiResponse = $apiResponse;
		$this->pageData = $pageData;
	}

	public function getText() {
		$pageData = $this->pageData->get( $this->apiResponse['rootid'] );
		return reset( $pageData['revisions'] )['*'];
	}
}

class LiquidThreadsApiImportSummary implements ImportSummary {
	protected $pageid, $pageData;

	public function __construct( $pageid, $pageData ) {
		$this->pageid = $pageid;
		$this->pageData = $pageData;
	}

	public function getText() {
		return $this->getPageData()['revisions']['*'];
	}

	public function getUser() {
		return User::newFromName( $this->getPageData()['user'], false );
	}

	protected function getPageData() {
		return $this->pageData->get( $this->pageid );
	}
}
