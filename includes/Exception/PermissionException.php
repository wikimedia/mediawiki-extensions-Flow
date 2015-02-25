<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class PermissionException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'insufficient-permission' );
	}

	/**
	 * Do not log exception resulting from user requesting
	 * disallowed content.
	 */
	function isLoggable() {
		return false;
	}
}

