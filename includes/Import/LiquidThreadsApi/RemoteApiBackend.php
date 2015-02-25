<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use ApiMain;
use Exception;
use FauxRequest;
use Flow\Container;
use Flow\Import\ImportException;
use Flow\Import\IImportSource;
use Flow\Import\ApiNullResponseException;
use Http;
use RequestContext;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UsageException;
use User;

class RemoteApiBackend extends ApiBackend {
	/**
	 * @param string
	 */
	protected $apiUrl;

	/**
	 * @param string|null
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
