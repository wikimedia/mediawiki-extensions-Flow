<?php

namespace Flow\Exception;

use ErrorPageError;

/**
 * Category: invalid input exception
 *
 * This is not logged, and must *only* be used when the error is caused by invalid end-user
 * input.  The same applies to the subclasses.
 *
 * If it is a logic error (including a missing or incorrect parameter not directly caused
 * by user input), or another kind of failure, another (loggable) exception must be used.
 */
class InvalidInputException extends ErrorPageError {

	public function __construct( $message, $code = 'default' ) {
		$list = $this->getErrorCodeList();
		if ( !in_array( $code, $list ) ) {
			$code = 'default';
		}
		parent::__construct( 'errorpagetitle', "flow-error-$code" );
	}

	protected function getErrorCodeList() {
		// Comments are i18n messages, for grepping
		return [
			'invalid-input',
			// flow-error-invalid-input
			'missing-revision',
			// flow-error-missing-revision
			'revision-comparison',
			// flow-error-revision-comparison
			'invalid-workflow',
			// flow-error-invalid-workflow
		];
	}

	public function report( $action = ErrorPageError::SEND_OUTPUT ) {
		global $wgOut;
		$wgOut->setStatusCode( 400 );
		parent::report( $action );
	}

}
