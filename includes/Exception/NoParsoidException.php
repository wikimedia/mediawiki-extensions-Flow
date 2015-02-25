<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class NoParsoidException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-wikitext' );
	}
}

