<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Category: Parsoid
 */
class NoParsoidException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-wikitext' );
	}
}
