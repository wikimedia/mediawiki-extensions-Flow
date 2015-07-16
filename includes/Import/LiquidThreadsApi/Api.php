<?php

namespace Flow\Import\LiquidThreadsApi;

use ApiBase;
use Flow\Import\ApiBackend\AbstractApiBackend;
use Flow\Import\ImportException;

class Api {
    /**
     * @var AbstractApiBackend
     */
    protected $apiBackend;

    /**
     * @param AbstractApiBackend $apiBackend
     */
    public function __construct( AbstractApiBackend $apiBackend ) {
        $this->apiBackend = $apiBackend;
    }

    /**
     * @return AbstractApiBackend
     */
    public function getBackend() {
        return $this->apiBackend;
    }

    /**
     * Retrieves LiquidThreads data from the API
     *
     * @param  array $conditions The parameters to pass to select the threads. Usually used in two ways: with thstartid/thpage, or with ththreadid
     * @return array Data as returned under query.threads by the API
     * @throws ApiNotFoundException Thrown when the remote api reports that the provided conditions
     *  have no matching records.
     * @throws ImportException When an error is received from the remote api.  This is often either
     *  a bad request or lqt threw an exception trying to respond to a valid request.
     */
    public function retrieveThreadData( array $conditions ) {
        $params = array(
            'action' => 'query',
            'list' => 'threads',
            'thprop' => 'id|subject|page|parent|ancestor|created|modified|author|summaryid|type|rootid|replies|signature',
            'rawcontinue' => 1, // We're doing continuation a different way, but this avoids a warning.
            'format' => 'json',
            'limit' => ApiBase::LIMIT_BIG1,
        );

        try {
            $data = $this->apiBackend->call( $params + $conditions );
        } catch ( ImportException $e ) {
            // LQT has some bugs where not finding the requested item in the
            // database throws returns this exception, which can be acceptable
            // and should be handled differently from other exceptions
            if ( $this->isNotFoundError( $e->getMessage() ) ) {
                throw new ApiNotFoundException( $e->getMessage() );
            }

            throw $e;
        }

        $firstThread = reset( $data['query']['threads'] );
        if ( !isset( $firstThread['replies'] ) ) {
            throw new ImportException( "Foreign API does not support reply exporting:" . json_encode( $data ) );
        }

        return $data['query']['threads'];
    }

    /**
     * Retrieves data about a set of pages from the API
     *
     * @param  array  $pageIds Page IDs to return data for.
     * @return array The query.pages part of the API response.
     * @throws \MWException
     */
    public function retrievePageDataById( array $pageIds ) {
        if ( !$pageIds ) {
            throw new \MWException( 'At least one page id must be provided' );
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
     * @throws \MWException
     * @throws ImportException
     */
    public function retrieveTopRevisionByTitle( array $titles ) {
        if ( !$titles ) {
            throw new \MWException( 'At least one title must be provided' );
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
     * @throws ApiNotFoundException Thrown when the remote api reports that the provided conditions
     *  have no matching records.
     * @throws ImportException When an error is received from the remote api.  This is often either
     *  a bad request or lqt threw an exception trying to respond to a valid request.
     * @throws ImportException When more revisions are available than can be returned in a single
     *  query and the calling code does not set $expectContinue to true.
     */
    public function retrievePageData( array $conditions, $expectContinue = false ) {
        $conditions += array(
            'action' => 'query',
            'prop' => 'revisions',
            'rvprop' => 'timestamp|user|content|ids',
            'format' => 'json',
            'rvlimit' => 5000,
            'rvdir' => 'newer',
            'continue' => '',
        );

        try {
            $data = $this->apiBackend->call( $conditions );
        } catch ( ImportException $e ) {
            // LQT has some bugs where not finding the requested item in the
            // database throws returns this exception, which can be acceptable
            // and should be handled differently from other exceptions
            if ( $this->isNotFoundError( $e->getMessage() ) ) {
                throw new ApiNotFoundException( $e->getMessage() );
            }

            throw $e;
        }

        if ( !$expectContinue && isset( $data['continue'] ) ) {
            throw new ImportException( 'More revisions than can be retrieved for conditions, import would be incomplete: ' . json_encode( $conditions ) );
        }

        return $data['query']['pages'];
    }

    /**
     * @param string $message Exception message
     * @return bool
     */
    protected function isNotFoundError( $message ) {
        $expect = 'Exception Caught: DatabaseBase::makeList: empty input for field thread_parent';
        return strpos( $message, $expect ) !== false;
    }
}
