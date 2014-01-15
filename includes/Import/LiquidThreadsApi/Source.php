<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use Flow\Import\ImportException;
use Flow\Import\IImportSource;
use Http;

class ImportSource implements IImportSource {
	/**
	 * @var ApiBackend
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pageName;

	/**
	 * @var CachedThreadData
	 */
	protected $threadData;

	/**
	 * @var CachedPageData
	 */
	protected $pageData;

	/**
	 * @param string $apiUrl
	 * @param string $pageName
	 */
	public function __construct( $apiUrl, $pageName ) {
		$this->api = new ApiBackend( $apiUrl );
		$this->pageName = $pageName;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTopics() {
		return new TopicIterator( $this, $this->threadData, $this->pageName );
	}

	/**
	 * @param integer $id
	 * @return ImportTopic
	 */
	public function getTopic( $id ) {
		return new ImportTopic( $this, $this->threadData->get( $id ) );
	}

	/**
	 * @param integer $id
	 * @return ImportPost
	 */
	public function getPost( $id ) {
		return new ImportPost( $this, $this->threadData->get( $id ) );
	}

	/**
	 * @param integer $id
	 * @return array
	 */
	public function getThreadData( $id ) {
		if ( is_array( $id ) ) {
			return $this->threadData->getMulti( $id );
		} else {
			return $this->threadData->get( $id );
		}
	}

	/**
	 * @param integer[]|integer $pageids
	 * @return array
	 */
	public function getPageData( $pageids ) {
		if ( is_array( $pageids ) ) {
			return $this->pageData->getMulti( $pageids );
		} else {
			return $this->pageData->get( $pageids );
		}
	}

	/**
	 * @param string $pageName
	 * @param integer $startId
	 * @return array
	 */
	public function getFromPage( $pageName, $startId = 0 ) {
		return $this->threadData->getFromPage( $pageName, $startId );
	}
}

class ApiBackend {
	public function __construct( $apiUrl ) {
		$this->apiUrl = $apiUrl;
	}

	public function retrieveThreadData( array $conds ) {
		$params = array(
			'action' => 'query',
			'list' => 'threads',
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid|type|rootid|replies',
			'format' => 'json',
			'limit' => ApiBase::LIMIT_BIG1,
		);
		$data = $this->apiCall( $params + $conds );

		if ( ! isset( $data['query']['threads'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				throw new ApiNotFoundException( "Did not find thread with conds: " . json_encode( $conds ) );
			} else {
				throw new ImportException( "Null response from API module", $data );
			}
		}

		$firstThread = reset( $data['query']['threads'] );
		if ( ! isset( $firstThread['replies'] ) ) {
			throw new ImportException( "Foreign API does not support reply exporting", $data );
		}

		return $data['query']['threads'];
	}

	public function retrievePageData( array $pageids ) {
		if ( !$pageids ) {
			throw new \MWException( 'At least one page id must be provided' );
		}
		$apiParams = array(
			'action' => 'query',
			'prop' => 'revisions',
			'pageids' => implode( '|', $pageids ),
			'rvprop' => 'timestamp|user|content',
			'format' => 'json',
			'limit' => ApiBase::LIMIT_BIG1,
		);

		$data = $this->apiCall( $apiParams );

		if ( ! isset( $data['query'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				throw new ApiNotFoundException( "Did not find pages: " . implode( ',', $pageids ) );
			} else {
				throw new ImportException( "Null response from API module", $data );
			}
		}

		$pages = $data['query']['pages'];

		return $pages;
	}

	public function apiCall( array $params ) {
		$url = wfAppendQuery( $this->apiUrl, $params );

		echo $url, "\n";
		$result = Http::get( $url );

		return json_decode( $result, true );
	}

	protected function isNotFoundError( $apiResponse ) {
		$expect = 'Exception Caught: DatabaseBase::makeList: empty input for field thread_parent';
		return $apiResponse['error']['info'] === $expect;
	}
}

class ApiNotFoundException extends ImportException {
}
