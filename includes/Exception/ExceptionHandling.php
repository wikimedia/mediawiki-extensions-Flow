<?php

namespace Flow\Exception;

use MWException;
use OutputPage;

/**
 * Flow base exception
 */
class FlowException extends MWException {

	/**
	 * Flow exception error code
	 * @var string
	 */
	protected $code;

	/**
	 * The output object
	 * @var OutputPage
	 */
	protected $output;

	/**
	 * @param string - The message from exception, used for debugging error
	 * @param string - The error code used to display error message
	 */
	public function __construct( $message, $code = 'default' ) {
		global $wgOut;
		parent::__construct( $message );
		$this->code = $code;
		// Set output object to the global $wgOut object by default
		$this->output = $wgOut;
	}

	/**
	 * Set the output object
	 */
	public function setOutput( OutputPage $output ) {
		$this->output = $output;
	}

	/**
	 * Get the message key for the localized error message
	 */
	public function getErrorCode() {
		$list = $this->getErrorCodeList();
		if ( !in_array( $this->code, $list ) ) {
			$this->code = 'default';
		}
		return 'flow-error-' . $this->code;
	}

	/**
	 * Error code list for this exception
	 */
	protected function getErrorCodeList() {
		return array ( 'default' );
	}

	/**
	 * Exception from API/commandline will be handled by MWException::report(),
	 * Overwrite the HTML display only
	 */
	public function reportHTML() {
		$this->output->setStatusCode( $this->getStatusCode() );
		$this->output->showErrorPage( 'errorpagetitle', $this->getErrorCode() );
		$this->output->output();
	}

	/**
	 * Default status code is 500, which is server error
	 */
	public function getStatusCode() {
		return 500;
	}
}

/**
 * Category: invalid input exception
 */
class InvalidInputException extends FlowException {
	protected function getErrorCodeList() {
		return array (
			'invalid-input',
			'missing-revision',
			'revision-comparison',
			'invalid-definition',
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

/**
 * Category: invalid action exception
 */
class InvalidActionException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'invalid-action'	);
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

/**
 * Category: commit failure exception
 */
class FailCommitException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'fail-commit' );
	}
}

/**
 * Category: permission related exception
 */
class PermissionException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'insufficient-permission' );
	}
}

/**
 * Category: invalid data exception
 */
class InvalidDataException extends FlowException {
	protected function getErrorCodeList() {
		return array (
			'invalid-title',
			'fail-load-data',
			'fail-load-history',
			'missing-topic-title'
		);
	}
}

/**
 * Category: data model processing exception
 */
class DataModelException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-data' );
	}
}

/**
 * Category: data persistency exception
 */
class DataPersistenceException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-data' );
	}
}

/**
 * Category: wikitext/html conversion exception
 */
class WikitextException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-wikitext' );
	}
}

/**
 * Category: Data Index
 */
class NoIndexException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'no-index' );
	}
}

class RuntimeException extends FlowException {
	protected function getErrorCodeList() {
		return array( 'other' );
	}
}
