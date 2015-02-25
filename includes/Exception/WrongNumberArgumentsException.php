<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class WrongNumberArgumentsException extends FlowException {
	/**
	 * @param array $args
	 * @param string $minExpected
	 * @param string|null $maxExpected
	 */
	public function __construct( array $args, $minExpected, $maxExpected = null ) {
		$count = count( $args );
		if ( $maxExpected === null ) {
			parent::__construct( "Expected $minExpected arguments but received $count" );
		} else {
			parent::__construct( "Expected between $minExpected and $maxExpected arguments but received $count" );
		}
	}
}

