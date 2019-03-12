<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use ApiErrorFormatter;
use ApiMain;
use ApiMessage;
use Exception;
use FauxRequest;
use Flow\Import\ImportException;
use Flow\Import\IImportSource;
use Http;
use RequestContext;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ApiUsageException;
use User;

class ImportSource implements IImportSource {
	// Thread types defined by LQT which are returned via api
	const THREAD_TYPE_NORMAL = 0;
	const THREAD_TYPE_MOVED = 1;
	const THREAD_TYPE_DELETED = 2;
	const THREAD_TYPE_HIDDEN = 4;

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
	 * @var int
	 */
	protected $cachedTopics = 0;

	/**
	 * @var User Used for scripted actions and occurances (such as suppression)
	 *  where the original user is not available.
	 */
	protected $scriptUser;

	/**
	 * @param ApiBackend $apiBackend
	 * @param string $pageName
	 * @param User $scriptUser
	 */
	public function __construct( ApiBackend $apiBackend, $pageName, User $scriptUser ) {
		$this->api = $apiBackend;
		$this->pageName = $pageName;
		$this->scriptUser = $scriptUser;

		$this->threadData = new CachedThreadData( $this->api );
		$this->pageData = new CachedPageData( $this->api );
	}

	/**
	 * Returns a system user suitable for assigning programatic actions to.
	 *
	 * @return User
	 */
	public function getScriptUser() {
		return $this->scriptUser;
	}

	/**
	 * @inheritDoc
	 */
	public function getHeader() {
		return new ImportHeader( $this->api, $this, $this->pageName );
	}

	/**
	 * @inheritDoc
	 */
	public function getTopics() {
		return new TopicIterator( $this, $this->threadData, $this->pageName );
	}

	/**
	 * @param int $id
	 * @return ImportTopic|null
	 */
	public function getTopic( $id ) {
		// reset our internal cached data every 100 topics. Otherwise imports
		// of any considerable size will take up large amounts of memory for
		// no reason, running into swap on smaller machines.
		$this->cachedTopics++;
		if ( $this->cachedTopics > 100 ) {
			$this->threadData->reset();
			$this->pageData->reset();
			$this->cachedTopics = 0;
		}

		$data = $this->threadData->get( $id );
		switch ( $data['type'] ) {
		// Standard thread
		case self::THREAD_TYPE_NORMAL:
			return new ImportTopic( $this, $data );

		// The topic no longer exists at the queried location, but
		// a stub was left behind pointing to it. This modified
		// version of ImportTopic gracefully adjusts the #REDIRECT
		// into a template to keep a similar output to lqt.
		case self::THREAD_TYPE_MOVED:
			return new MovedImportTopic( $this, $data );

		// To get these back from the api we would have to send the `showdeleted`
		// query param.  As we are not requesting them, just ignore for now.
		case self::THREAD_TYPE_DELETED:
			return null;

		// Was assigned but never used by LQT.
		case self::THREAD_TYPE_HIDDEN:
			return null;
		}
	}

	/**
	 * @param int $id
	 * @return ImportPost
	 */
	public function getPost( $id ) {
		return new ImportPost( $this, $this->threadData->get( $id ) );
	}

	/**
	 * @param int $id
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
	 * @param int[]|int $pageIds
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
	 * @param int $startId
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
			[ 'lqt-api', $this->getApiKey() ],
			func_get_args()
		);

		return implode( ':', $components );
	}
}

abstract class ApiBackend implements LoggerAwareInterface {

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	public function __construct() {
		$this->logger = new NullLogger;
	}

	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Retrieves LiquidThreads data from the API
	 *
	 * @param array $conditions The parameters to pass to select the threads.
	 *  Usually used in two ways: with thstartid/thpage, or with ththreadid
	 * @return array Data as returned under query.threads by the API
	 * @throws ApiNotFoundException Thrown when the remote api reports that the provided conditions
	 *  have no matching records.
	 * @throws ImportException When an error is received from the remote api.  This is often either
	 *  a bad request or lqt threw an exception trying to respond to a valid request.
	 */
	public function retrieveThreadData( array $conditions ) {
		$params = [
			'action' => 'query',
			'list' => 'threads',
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid' .
				'|type|rootid|replies|signature',
			'rawcontinue' => 1, // We're doing continuation a different way, but this avoids a warning.
			'format' => 'json',
			'limit' => ApiBase::LIMIT_BIG1,
		];
		$data = $this->apiCall( $params + $conditions );

		if ( !isset( $data['query']['threads'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				$message = "Did not find thread with conditions: " . json_encode( $conditions );
				$this->logger->debug( __METHOD__ . ": $message" );
				throw new ApiNotFoundException( $message );
			} else {
				$this->logger->error( __METHOD__ . ': Failed API call against ' . $this->getKey() .
					' with conditions : ' . json_encode( $conditions ) );
				throw new ImportException( "Null response from API module:" . json_encode( $data ) );
			}
		}

		$firstThread = reset( $data['query']['threads'] );
		if ( !isset( $firstThread['replies'] ) ) {
			throw new ImportException( "Foreign API does not support reply exporting:" .
				json_encode( $data ) );
		}

		return $data['query']['threads'];
	}

	/**
	 * Retrieves data about a set of pages from the API
	 *
	 * @param int[] $pageIds Page IDs to return data for.
	 * @return array The query.pages part of the API response.
	 * @throws \MWException
	 */
	public function retrievePageDataById( array $pageIds ) {
		if ( !$pageIds ) {
			throw new \MWException( 'At least one page id must be provided' );
		}

		return $this->retrievePageData( [
			'pageids' => implode( '|', $pageIds ),
		] );
	}

	/**
	 * Retrieves data about the latest revision of the titles
	 * from the API
	 *
	 * @param string[] $titles Titles to return data for
	 * @return array The query.pages prt of the API response.
	 * @throws \MWException
	 * @throws ImportException
	 */
	public function retrieveTopRevisionByTitle( array $titles ) {
		if ( !$titles ) {
			throw new \MWException( 'At least one title must be provided' );
		}

		return $this->retrievePageData( [
			'titles' => implode( '|', $titles ),
			'rvlimit' => 1,
			'rvdir' => 'older',
		], true );
	}

	/**
	 * Retrieves data about a set of pages from the API
	 *
	 * @param array $conditions Conditions to retrieve pages by; to be sent to the API.
	 * @param bool $expectContinue Pass true here when caller expects more revisions to exist than
	 *  they are requesting information about.
	 * @return array The query.pages part of the API response.
	 * @throws ApiNotFoundException Thrown when the remote api reports that the provided conditions
	 *  have no matching records.
	 * @throws ImportException When an error is received from the remote api.  This is often either
	 *  a bad request or lqt threw an exception trying to respond to a valid request.
	 * @throws ImportException When more revisions are available than can be returned in a single
	 *  query and the calling code does not set $expectContinue to true.
	 */
	public function retrievePageData( array $conditions, $expectContinue = false ) {
		$conditions += [
			'action' => 'query',
			'prop' => 'revisions',
			'rvprop' => 'timestamp|user|content|ids',
			'format' => 'json',
			'rvlimit' => 5000,
			'rvdir' => 'newer',
			'continue' => '',
		];
		$data = $this->apiCall( $conditions );

		if ( !isset( $data['query'] ) ) {
			if ( $this->isNotFoundError( $data ) ) {
				$message = "Did not find pages: " . json_encode( $conditions );
				$this->logger->debug( __METHOD__ . ": $message" );
				throw new ApiNotFoundException( $message );
			} else {
				$this->logger->error( __METHOD__ . ': Failed API call against ' . $this->getKey() .
					' with conditions : ' . json_encode( $conditions ) );
				throw new ImportException( "Null response from API module: " . json_encode( $data ) );
			}
		} elseif ( !$expectContinue && isset( $data['continue'] ) ) {
			throw new ImportException( "More revisions than can be retrieved for conditions, import would" .
				" be incomplete: " . json_encode( $conditions ) );
		}

		return $data['query']['pages'];
	}

	/**
	 * Calls the remote API
	 *
	 * @param array $params The API request to send
	 * @param int $retry Retry the request on failure this many times
	 * @return array API return value, decoded from JSON into an array.
	 */
	abstract public function apiCall( array $params, $retry = 1 );

	/**
	 * @return string A unique identifier for this backend.
	 */
	abstract public function getKey();

	/**
	 * @param array $apiResponse
	 * @return bool
	 */
	protected function isNotFoundError( $apiResponse ) {
		// LQT has some bugs where not finding the requested item in the database
		// returns this exception.
		$expect = 'Exception Caught: Wikimedia\\Rdbms\\Database::makeList: empty input for field thread_parent';
		return false !== strpos( $apiResponse['error']['info'], $expect );
	}
}

class RemoteApiBackend extends ApiBackend {
	/**
	 * @var string
	 */
	protected $apiUrl;

	/**
	 * @var string|null
	 */
	protected $cacheDir;

	/**
	 * @param string $apiUrl
	 * @param string|null $cacheDir
	 */
	public function __construct( $apiUrl, $cacheDir = null ) {
		parent::__construct();
		$this->apiUrl = $apiUrl;
		$this->cacheDir = $cacheDir;
	}

	public function getKey() {
		return $this->apiUrl;
	}

	public function apiCall( array $params, $retry = 1 ) {
		$params['format'] = 'json';
		$url = wfAppendQuery( $this->apiUrl, $params );
		$file = $this->cacheDir . '/' . md5( $url ) . '.cache';
		$this->logger->debug( __METHOD__ . ": $url" );
		if ( $this->cacheDir && file_exists( $file ) ) {
			$result = file_get_contents( $file );
		} else {
			do {
				$result = Http::get( $url );
			} while ( $result === false && --$retry >= 0 );

			if ( $this->cacheDir && file_put_contents( $file, $result ) === false ) {
				$this->logger->warning( "Failed writing cached api result to $file" );
			}
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
		parent::__construct();
		$this->user = $user;
	}

	public function getKey() {
		return 'local';
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
			return $api->getResult()->getResultData( null, [ 'Strip' => 'all' ] );
		} catch ( ApiUsageException $exception ) {
			// Mimic the behaviour when called remotely
			$errors = $exception->getStatusValue()->getErrorsByType( 'error' );
			if ( !$errors ) {
				$errors = $exception->getStatusValue()->getErrorsByType( 'warning' );
			}
			if ( !$errors ) {
				$errors = [ [ 'message' => 'unknownerror-nocode', 'params' => [] ] ];
			}
			$msg = ApiMessage::create( $errors[0] );
			return [
				'error' => [
					'code' => $msg->getApiCode(),
					'info' => ApiErrorFormatter::stripMarkup(
						// @phan-suppress-next-line PhanUndeclaredMethod Phan is mostly right
						$msg->inLanguage( 'en' )->useDatabase( 'false' )->text()
					),
				] + $msg->getApiData()
			];
		} catch ( Exception $exception ) {
			// Mimic behaviour when called remotely
			return [
				'error' => [
					'code' => 'internal_api_error_' . get_class( $exception ),
					'info' => 'Exception Caught: ' . $exception->getMessage(),
				],
			];
		}
	}
}
