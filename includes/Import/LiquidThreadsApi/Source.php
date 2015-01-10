<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use ApiMain;
use Exception;
use FauxRequest;
use Flow\Container;
use Flow\Import\ImportException;
use Flow\Import\IImportSource;
use Http;
use RequestContext;
use UsageException;
use User;

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
	 * @param ApiBackend $apiBackend
	 * @param string $pageName
	 */
	public function __construct( ApiBackend $apiBackend, $pageName ) {
		$this->api = $apiBackend;
		$this->pageName = $pageName;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeader() {
		$controller = Container::get( 'occupation_controller' );

		return new ImportHeader( $this->api, $this, $this->pageName, $controller->getTalkpageManager() );
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
	 * @param integer[]|integer $pageIds
	 * @return array
	 */
	public function getPageData( $pageIds ) {
		if ( is_array( $pageIds ) ) {
			return $this->pageData->getMulti( $pageIds );
		} else {
			return $this->pageData->get( $pageIds );
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
	 * Gets a unique identifier for the wiki being imported
	 * @return string Usually either a string 'local' or an API URL
	 */
	public function getApiKey() {
		return $this->api->getKey();
	}

	/**
	 * Returns a key uniquely representing an object determined by arguments.
	 * Parameters: Zero or more strings that uniquely represent the object
	 * for this ImportSource
	 *
	 * @return string Unique key
	 */
	public function getObjectKey( /* $args */ ) {
		$components = array_merge(
			array( 'lqt-api', $this->getApiKey() ),
			func_get_args()
		);

		return implode( ':', $components );
	}
}

abstract class ApiBackend {
	/**
	 * Retrieves LiquidThreads data from the API
	 *
	 * @param  array  $conditions The parameters to pass to select the threads. Usually used in two ways: with thstartid/thpage, or with ththreadid
	 * @return array Data as returned under query.threads by the API
	 * @throws ApiNotFoundException
	 * @throws ImportException
	 */
	public function retrieveThreadData( array $conditions ) {
		$params = array(
			'action' => 'query',
			'list' => 'threads',
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid|type|rootid|replies',
			'format' => 'json',
			'limit' => ApiBase::LIMIT_BIG1,
		);
		$data = $this->apiCall( $params + $conditions );

		if ( ! isset( $data['query']['threads'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				throw new ApiNotFoundException( "Did not find thread with conditions: " . json_encode( $conditions ) );
			} else {
				throw new ImportException( "Null response from API module:" . json_encode( $data ) );
			}
		}

		$firstThread = reset( $data['query']['threads'] );
		if ( ! isset( $firstThread['replies'] ) ) {
			throw new ImportException( "Foreign API does not support reply exporting:" . json_encode( $data ) );
		}

		return $data['query']['threads'];
	}

	/**
	 * Retrieves data about a set of pages from the API
	 *
	 * @param  array  $pageIds Page IDs to return data for.
	 * @return array The query.pages part of the API response.
	 * @throws \Exception
	 */
	public function retrievePageDataById( array $pageIds ) {
		if ( !$pageIds ) {
			throw new \Exception( 'At least one page id must be provided' );
		}

		return $this->retrievePageData( array(
			'pageids' => implode( '|', $pageIds ),
		) );
	}

	/**
	 * Retrieves data about the latest revision of the titles
	 * from the API
	 *
	 * @param string[] $titles Titles to return data for
	 * @return array The query.pages prt of the API response.
	 * @throws \Exception
	 * @throws ImportException
	 */
	public function retrieveTopRevisionByTitle( array $titles ) {
		if ( !$titles ) {
			throw new \Exception( 'At least one title must be provided' );
		}

		return $this->retrievePageData( array(
			'titles' => implode( '|', $titles ),
			'rvlimit' => 1,
			'rvdir' => 'older',
		), true );
	}

	/**
	 * Retrieves data about a set of pages from the API
	 *
	 * @param array $conditions     Conditions to retrieve pages by; to be sent to the API.
	 * @param bool  $expectContinue Pass true here when caller expects more revisions to exist than
	 *  they are requesting information about.
	 * @return array The query.pages part of the API response.
	 * @throws ApiNotFoundException
	 * @throws ImportException
	 */
	public function retrievePageData( array $conditions, $expectContinue = false ) {
		$data = $this->apiCall( $conditions + array(
			'action' => 'query',
			'prop' => 'revisions',
			'rvprop' => 'timestamp|user|content|ids',
			'format' => 'json',
			'rvlimit' => 5000,
			'rvdir' => 'newer',
			'continue' => '',
			'limit' => ApiBase::LIMIT_BIG1,
		) );

		if ( ! isset( $data['query'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				throw new ApiNotFoundException( "Did not find pages: " . json_encode( $conditions ) );
			} else {
				throw new ImportException( "Null response from API module: " . json_encode( $data ) );
			}
		} elseif ( !$expectContinue && isset( $data['continue'] ) ) {
			wfWarn( "More revisions than can be retrieved for conditions: " . json_encode( $conditions ) );
		}

		return $data['query']['pages'];
	}

	/**
	 * Calls the remote API
	 *
	 * @param array $params The API request to send
	 * @param int   $retry  Retry the request on failure this many times
	 * @return array API return value, decoded from JSON into an array.
	 */
	abstract function apiCall( array $params, $retry = 1 );

	/**
	 * @return string A unique identifier for this backend.
	 */
	abstract function getKey();

	/**
	 * @param array $apiResponse
	 * @return bool
	 */
	protected function isNotFoundError( $apiResponse ) {
		$expect = 'Exception Caught: DatabaseBase::makeList: empty input for field thread_parent';
		return $apiResponse['error']['info'] === $expect;
	}
}

class RemoteApiBackend extends ApiBackend {
	public function __construct( $apiUrl ) {
		$this->apiUrl = $apiUrl;
	}

	/**
	 * Returns the URL being used for this backend.
	 *
	 * @return string API URL
	 */
	public function getKey() {
		return $this->apiUrl;
	}

	public function apiCall( array $params, $retry = 1 ) {
		$params['format'] = 'json';
		$url = wfAppendQuery( $this->apiUrl, $params );

		$result = Http::get( $url );
		if ( $result === false && $retry > 0 ) {
			return $this->apiCall( $params, $retry - 1 );
		}
		return json_decode( $result, true );
	}
}

class LocalApiBackend extends ApiBackend {
	/**
	 * @var User|null
	 */
	protected $user;

	public function __construct( User $user = null ) {
		$this->user = $user;
	}

	public function apiCall( array $params, $retry = 1 ) {
		try {
			$context = new RequestContext;
			$context->setRequest( new FauxRequest( $params ) );
			if ( $this->user ) {
				$context->setUser( $this->user );
			}

			$api = new ApiMain( $context );
			$api->execute();
			return $api->getResult()->getData();
		} catch ( UsageException $exception ) {
			// Mimic the behaviour when called remotely
			return array( 'error' => $exception->getMessageArray() );
		} catch ( Exception $exception ) {
			// Mimic behaviour when called remotely
			return array(
				'error' => array(
					'code' => 'internal_api_error_' . get_class( $exception ),
					'info' => 'Exception Caught: ' . $exception->getMessage(),
				),
			);
		}
	}

	public function getKey() {
		return 'local';
	}
}

class ApiNotFoundException extends ImportException {
}
