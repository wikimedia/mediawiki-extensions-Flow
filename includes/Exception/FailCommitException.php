<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class FailCommitException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'fail-commit' );
	}
}

