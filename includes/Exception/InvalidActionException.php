<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Category: invalid action exception
 */
class InvalidActionException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'invalid-action'	);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPageTitle() {
		return $this->parsePageTitle( 'nosuchaction' );
	}

	/**
	 * Bad request
	 */
	public function getStatusCode() {
		return 400;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHTML() {
		// we only want a nice error message here, no stack trace
		$rc = new RequestContext();
		$output = $rc->getOutput();
		$output->showErrorPage( $this->getPageTitle(), $this->getErrorCode() );
		return $output->getHTML();
	}

	/**
	 * Do not log exception resulting from input error
	 */
	function isLoggable() {
		return false;
	}
}
