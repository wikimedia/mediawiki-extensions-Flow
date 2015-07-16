<?php

namespace Flow\Import\ApiBackend;

use Flow\Exception\FlowException;
use Http;

class RemoteApiBackend extends AbstractApiBackend {
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

    /**
     * {$@inheritdoc}
     */
    protected function doCall( array $params, $retry = 1 ) {
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

            if ( $result === false ) {
                // HTTP failure
                throw new FlowException( "Failed to call $url" );
            } elseif ( $this->cacheDir && file_put_contents( $file, $result ) === false ) {
                $this->logger->warning( "Failed writing cached api result to $file" );
            }
        }

        $result = json_decode( $result, true );

        if ( isset( $result['error']['info'] ) ) {
            // API call failure
            throw new FlowException( $result['error']['info'] );
        }

        return $result;
    }
}
