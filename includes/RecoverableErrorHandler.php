<?php

namespace Flow;

/**
 * Catches E_RECOVERABLE_ERROR and converts into exceptions
 * instead of fataling.
 *
 * Usage:
 *  set_error_handler( new RecoverableErrorHandler, E_RECOVERABLE_ERROR );
 *  try {
 *      ...
 *  } catch ( CatchableFatalErrorException $fatal ) {
 *
 *  }
 *  restore_error_handler();
 */
class RecoverableErrorHandler {
	public function __invoke( $errno, $errstr, $errfile, $errline ) {
		if ( $errno !== E_RECOVERABLE_ERROR ) {
			return false;
		}

		throw new CatchableFatalErrorException( $errno, $errstr, $errfile, $errline );
	}
}
