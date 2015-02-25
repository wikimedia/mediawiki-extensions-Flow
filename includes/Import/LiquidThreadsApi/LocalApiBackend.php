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
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				return ApiResult::removeMetadata( $api->getResult()->getResultData() );
			} else {
				return $api->getResult()->getData();
			}
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
}

