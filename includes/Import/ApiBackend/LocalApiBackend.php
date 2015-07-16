<?php

namespace Flow\Import\ApiBackend;

use ApiMain;
use FauxRequest;
use RequestContext;
use User;

class LocalApiBackend extends AbstractApiBackend {
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

    /**
     * {@inheritdoc}
     */
    protected function doCall( array $params, $retry = 1 ) {
        $context = new RequestContext;
        $context->setRequest( new FauxRequest( $params ) );
        if ( $this->user ) {
            $context->setUser( $this->user );
        }

        $api = new ApiMain( $context );

        $api->execute();
        return $api->getResult()->getResultData( null, array( 'Strip' => 'all' ) );
    }
}
