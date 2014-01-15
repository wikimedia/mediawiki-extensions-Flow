<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Iterator;
use User;

class ImportPost implements IImportPost {
	/**
	 * @var ImportSource
	 */
	protected $importSource;

	/**
	 * @var array
	 */
	protected $apiResponse;

	/**
	 * @param ImportSource $source
	 * @param array $apiResponse
	 */
	public function __construct( ImportSource $source, array $apiResponse ) {
		$this->importSource = $source;
		$this->apiResponse = $apiResponse;
	}

	/**
	 * @return User
	 */
	public function getAuthor() {
		return User::newFromName( $this->apiResponse['author']['name'], false );
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
}

/**
 * This is a bit funny because LQT always has a single top-level reply
 * to the topic.
 */
class TopLevelImportPost implements IImportPost {
	/**
	 * @var ImportTopic
	 */
	protected $topic;

	public function __construct( ImportTopic $topic ) {
		$this->topic = $topic;
	}

	public function getAuthor() {
		return $this->topic->getAuthor();
	}

	public function getCreatedTimestamp() {
		return $this->topic->getCreatedTimestamp();
	}

	public function getModifiedTimestamp() {
		return $this->topic->getModifiedTimestamp();
	}

	public function getText() {
		return $this->topic->getTopReplyContent();
	}

	public function getReplies() {
		return new ReplyIterator( $this->topic );
	}

	public function getApiResponse() {
		return $this->topic->getApiResponse();
	}

	public function getSource() {
		return $this->topic->getSource();
	}
}

class ImportTopic extends ImportPost implements IImportTopic {
	/**
	 * @return string
	 */
	public function getText() {
		return $this->apiResponse['subject'];
	}

	/**
	 * @return string
	 */
	public function getTopReplyContent() {
		return parent::getText();
	}

	/**
	 * @return boolean
	 */
	public function hasSummary() {
		return $this->getSummaryId() > 0;
	}

	/**
	 * @return IImportSummary|null
	 */
	public function getTopicSummary() {
		if ( $this->hasSummary() ) {
			return new ImportSummary( $this->importSource->getPageData( $this->getSummaryId() ) );
		} else {
			return null;
		}
	}

	/**
	 * This is a bit funny because LQT always has a single top-level reply
	 * to the topic.
	 *
	 * @return Iterator<IImportPost>
	 */
	public function getReplies() {
		return new ArrayIterator( array(
			new TopLevelImportPost( $this )
		) );
	}

	/**
	 * @return integer
	 */
	protected function getSummaryId() {
		return $this->apiResponse['summaryid'];
	}
}

class ImportSummary implements IImportSummary {
	/**
	 * @var array
	 */
	protected $apiResponse;

	/**
	 * @param array $apiResponse
	 */
	public function __construct( array $apiResponse ) {
		$this->apiResponse = $apiResponse;
	}

	/**
	 * @return User
	 */
	public function getAuthor() {
		// @todo what about > 0?
		return User::newFromName( $this->apiResponse['revisions'][0]['user'], false );
	}

	/**
	 * @return string
	 */
	public function getText() {
		// @todo what about > 0?
		return $this->apiResponse['revisions'][0]['*'];
	}
}
