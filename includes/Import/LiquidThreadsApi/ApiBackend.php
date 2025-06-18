<?php

namespace Flow\Import\LiquidThreadsApi;

use Flow\Import\ImportException;
use InvalidArgumentException;
use MediaWiki\Api\ApiBase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class ApiBackend implements LoggerAwareInterface {

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	public function __construct() {
		$this->logger = new NullLogger;
	}

	public function setLogger( LoggerInterface $logger ): void {
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
			'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid' . '|type|rootid|replies|signature',
			'rawcontinue' => 1,
			// We're doing continuation a different way, but this avoids a warning.
			'format' => 'json',
			'limit' => ApiBase::LIMIT_BIG1,
		];
		$data = $this->apiCall( $params + $conditions );

		if ( !isset( $data['query']['threads'] ) ) {
			$this->logger->error(
				__METHOD__ . ': Failed API call against ' . $this->getKey() .
				' with conditions : ' . json_encode( $conditions )
			);
			throw new ImportException(
				"Null response from API module:" . json_encode( $data )
			);
		}

		$threads = $data['query']['threads'];
		if ( !$threads ) {
			$message = "Did not find thread with conditions: " . json_encode( $conditions );
			$this->logger->debug( __METHOD__ . ": $message" );
			throw new ApiNotFoundException( $message );
		}
		$firstThread = reset( $threads );
		if ( !isset( $firstThread['replies'] ) ) {
			throw new ImportException(
				"Foreign API does not support reply exporting:" . json_encode( $data )
			);
		}

		return $threads;
	}

	/**
	 * Retrieves data about a set of pages from the API
	 *
	 * @param int[] $pageIds Page IDs to return data for. There must be at least one element.
	 * @phan-param non-empty-list<int> $pageIds
	 * @return array The query.pages part of the API response.
	 */
	public function retrievePageDataById( array $pageIds ) {
		if ( !$pageIds ) {
			throw new InvalidArgumentException( 'At least one page id must be provided' );
		}

		return $this->retrievePageData(
			[
				'pageids' => implode( '|', $pageIds ),
			]
		);
	}

	/**
	 * Retrieves data about the latest revision of the titles
	 * from the API
	 *
	 * @param string[] $titles Titles to return data for. There must be at least one element.
	 * @phan-param non-empty-list<string> $titles
	 * @return array The query.pages part of the API response.
	 * @throws ImportException
	 */
	public function retrieveTopRevisionByTitle( array $titles ) {
		if ( !$titles ) {
			throw new InvalidArgumentException( 'At least one title must be provided' );
		}

		return $this->retrievePageData(
			[
				'titles' => implode( '|', $titles ),
				'rvlimit' => 1,
				'rvdir' => 'older',
			],
			true
		);
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
			$this->logger->error(
				__METHOD__ . ': Failed API call against ' . $this->getKey() .
				' with conditions : ' . json_encode( $conditions )
			);
			throw new ImportException(
				"Null response from API module: " . json_encode( $data )
			);
		} elseif ( !$expectContinue && isset( $data['continue'] ) ) {
			throw new ImportException(
				"More revisions than can be retrieved for conditions, import would" . " be incomplete: " . json_encode(
					$conditions
				)
			);
		}
		if ( !$data['query']['pages'] ) {
			$message = "Did not find pages: " . json_encode( $conditions );
			$this->logger->debug( __METHOD__ . ": $message" );
			throw new ApiNotFoundException( $message );
		}
		$dataProcessed = [];
		// Reprocess to a formatversion=1-esque format for compatibility
		// with the CachedData structure
		foreach ( $data['query']['pages'] as $page ) {
			$dataProcessed[(int)$page['pageid']] = $page;
		}
		return $dataProcessed;
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
}
