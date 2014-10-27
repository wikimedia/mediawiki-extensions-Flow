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

	/**
	 * Gets the API URL being used for importing
	 * @return string
	 */
	public function getApiUrl() {
		return $this->api->getUrl();
	}

	/**
	 * Returns a key uniquely representing an object determined by arguments.
	 * Parameters: Zero or more strings that uniquely represent the object
	 * for this ImportSource
	 * @return string Unique key
	 */
	public function getObjectKey( /* $args */ ) {
		$components = array_merge(
			array( 'lqt-api', $this->getApiUrl() ),
			func_get_args()
		);

		return implode( ':', $components );
	}
}

class ApiBackend {
	public function __construct( $apiUrl ) {
		$this->apiUrl = $apiUrl;
	}

	/**
	 * Retrieves LiquidThreads data from the API
	 * @param  array  $conds The parameters to pass to select the threads.
	 * Usually used in two ways: with thstartid/thpage, or with ththreadid
	 * @return array Data as returned under query.threads by the API
	 */
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

	/**
	 * Retrieves data about a set of pages from the API
	 * @param  array  $pageids Page IDs to return data for.
	 * @return array The query.pages part of the API response.
	 */
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

	/**
	 * Calls the remote API
	 * @param  array  $params The API request to send
	 * @return array API return value, decoded from JSON into an array.
	 */
	public function apiCall( array $params ) {
		$params['format'] = 'json';
		$url = wfAppendQuery( $this->apiUrl, $params );

		//echo $url, "\n";
		$result = Http::get( $url );

		return json_decode( $result, true );
	}

	/**
	 * Returns the URL being used for this backend.
	 * @return string API URL
	 */
	public function getUrl() {
		return $this->apiUrl;
	}

	protected function isNotFoundError( $apiResponse ) {
		$expect = 'Exception Caught: DatabaseBase::makeList: empty input for field thread_parent';
		return $apiResponse['error']['info'] === $expect;
	}
}

class ApiNotFoundException extends ImportException {
}
