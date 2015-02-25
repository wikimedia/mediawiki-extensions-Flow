<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class NoIndexException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'no-index' );
	}
}

