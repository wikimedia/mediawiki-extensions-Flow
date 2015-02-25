<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

/**
 * Specific exception thrown when a workflow is requested by id through
 * WorkflowLoaderFactory and it does not exist.
 */
class UnknownWorkflowIdException extends InvalidInputException {
	protected function getErrorCodeList() {
		return array( 'invalid-input' );
	}

	public function getHTML() {
		return wfMessage( 'flow-error-unknown-workflow-id' )->escaped();
	}

	public function getPageTitle() {
		return wfMessage( 'flow-error-unknown-workflow-id-title' )->escaped();
	}
}
