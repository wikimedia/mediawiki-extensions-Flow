<?php

namespace Flow\Exception;

use MWExceptionHandler;
use MWException;

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
		$this->output = $wgOut;
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

	public function report() {
		MWExceptionHandler::logException( $this );
		$this->output->showErrorPage( 'errorpagetitle', $this->getErrorCode() );
		$this->output->output();
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

	public function report() {
		$this->output->setStatusCode( 400 );
		parent::report();
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

	public function report() {
		$this->output->setStatusCode( 400 );
		parent::report();
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
