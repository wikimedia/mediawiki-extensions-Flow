<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Category: invalid data exception
 */
class InvalidDataException extends FlowException {
	protected function getErrorCodeList() {
		return array (
			'invalid-title',
			'fail-load-data',
			'fail-load-history',
			'missing-topic-title',
			'missing-metadata'
		);
	}
}
