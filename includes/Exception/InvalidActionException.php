<?php

namespace Flow\Exception;

use ErrorPageError;

/**
 * Category: invalid action exception
 */
class InvalidActionException extends ErrorPageError {

	public function __construct( $message, $code = 'default' ) {
		$list = $this->getErrorCodeList();
		if ( !in_array( $code, $list ) ) {
			$code = 'default';
		}
		parent::__construct( 'nosuchaction', "flow-error-$code" );
	}

	protected function getErrorCodeList() {
		// Comments are i18n messages, for grepping
		return [
			'invalid-action',
			// flow-error-invalid-action
		];
	}

	public function report( $action = ErrorPageError::SEND_OUTPUT ) {
		global $wgOut;
		$wgOut->setStatusCode( 400 );
		parent::report( $action );
	}

}
