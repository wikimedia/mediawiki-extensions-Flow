<?php

namespace Flow\Exception;

/**
 * Category: invalid data exception
 */
class InvalidDataException extends FlowException {
	protected function getErrorCodeList() {
		return [
			'invalid-title',
			// flow-error-invalid-title
			'fail-load-data',
			// flow-error-fail-load-data
			'fail-load-history',
			// flow-error-fail-load-history
			'missing-topic-title',
			// flow-error-missing-topic-title
			'missing-metadata',
			// flow-error-missing-metadata
			'different-page',
			// flow-error-different-page
		];
	}
}
