<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
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

	public function getObjectKey() {
		return $this->importSource->getObjectKey( 'thread_id', $this->apiResponse['id'] );
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

	public function getObjectKey() {
		return $this->topic->getObjectKey();
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
	/** @var ImportSource **/
	protected $source;

	/**
	 * @param array $apiResponse
	 * @throws ImportException
	 */
	public function __construct( array $apiResponse, ImportSource $source ) {
		$this->importSource = $source;
		$this->apiResponse = $apiResponse;
		if ( !isset( $apiResponse['revisions'][0] ) ) {
			throw new ImportException( 'Invalid summary' );
		}
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

	/**
	 * @return string
	 */
	public function getCreatedTimestamp() {
		return $this->apiResponse['revisions'][0]['timestamp'];
	}

	public function getObjectKey() {
		return $this->importSource->getObjectKey( 'summary_id', $this->apiResponse['pageid'] );
	}
}
