<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class InvalidInputException extends FlowException {
	protected function getErrorCodeList() {
		return array (
			'invalid-input',
			'missing-revision',
			'revision-comparison',
			'invalid-workflow'
		);
	}

	/**
	 * Bad request
	 */
	public function getStatusCode() {
		return 400;
	}

	/**
	 * Do not log exception resulting from input error
	 */
	function isLoggable() {
		return false;
	}
}

