<?php

namespace Flow\Import\ApiBackend;

use Exception;
use Flow\Import\ImportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractApiBackend implements LoggerAwareInterface {
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
     * @return string A unique identifier for this backend.
     */
    abstract public function getKey();

    /**
     * Performs the actual API call.
     *
     * @param array $params The API request to send
     * @param int $retry Retry the request on failure this many times
     * @return array API return value, decoded from JSON into an array.
     * @throws Exception
     */
    abstract protected function doCall( array $params, $retry = 1 );

    /**
     * Calls the API and returns the result, or throws an
     * ImportException if the API call failed.
     *
     * @param array $params The API request to send
     * @param int $retry Retry the request on failure this many times
     * @return array API return value, decoded from JSON into an array.
     * @throws ImportException
     */
    public function call( array $params, $retry = 1 ) {
        try {
            return $this->call( $params, $retry );
        } catch ( Exception $e ) {
            $this->logger->error( __METHOD__ . ': Failed API call against ' . $this->getKey() . ' with params: ' . json_encode( $params ) );
            throw new ImportException( $e->getMessage() );
        }
    }
}
