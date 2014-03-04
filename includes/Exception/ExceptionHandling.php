<?php

namespace Flow\Exception;

use MWException;
use ErrorPageError;
use PermissionsError;
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
	 * Override parent method: we can use wfMessage here
	 *
	 * @return bool
	 */
	public function useMessageCache() {
		return true;
	}

	/**
	 * Overrides MWException getHTML, adding a more human-friendly error message
	 *
	 * @return string
	 */
	public function getHTML() {
		/*
		 * We'll want both a proper humanized error msg & the stacktrace the
		 * parent exception handler generated.
		 * We'll create a stub OutputPage object here, to use its showErrorPage
		 * to add our own humanized error message. Then we'll append the stack-
		 * trace (parent::getHTML) and then just return the combined HTML.
		 */
		$output = new OutputPage();
		$output->showErrorPage( $this->getErrorPageTitle(), $this->getErrorCode() );
		$output->addHTML( parent::getHTML() );
		return $output->getHTML();
	}

	/**
	 * Error page title
	 */
	protected function getErrorPageTitle() {
		return 'errorpagetitle';
	}

	/**
	 * Exception from API/commandline will be handled by MWException::report(),
	 * Overwrite the HTML display only
	 */
	public function reportHTML() {
		$this->output->setStatusCode( $this->getStatusCode() );

		/*
		 * Parent exception handler uses global $wgOut
		 * We want to play nice and do inheritance and all, but that means we'll
		 * have to cheat here and assign out $this->output to $wgOut in order
		 * to have parent::reportHTML use the correct OutputPage object.
		 * After that, restore original $wgOut.
		 */
		global $wgOut;
		$wgOutBkp = $wgOut;
		$wgOut = $this->output;
		parent::reportHTML(); // this will do ->output() already
		$wgOut = $wgOutBkp;
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
class InvalidInputException extends ErrorPageError {
	function __construct( $debugText, $flowKey ) {
		parent::__construct( 'flow-error-' . $flowKey, 'flow-invalidinput-errortext' );
		// @todo: Log the $flowKey and $debugText with the HTTP Referer to a Flow
		//        log so we can find callers of Flow supplying bad input params.
	}

	/**
	 * Just like BadTitleError in core. Core should call getStatusCode and use that.
	 */
	function report() {
		global $wgOut;

		$wgOut->setStatusCode( 400 );
		parent::report();
	}

}

/**
 * Category: invalid action exception
 */
class InvalidActionException extends ErrorPageError {
	function __construct( $debugText, $flowKey ) {
		// @todo: Remove $flowKey from throw new InvalidActionException, since
		//        it is always invalid-action.
		parent::__construct( 'nosuchaction', 'flow-error-invalid-action' );
		// @todo: Log the $debugText with the HTTP Referer to a Flow log so we
		//        can find callers of Flow supplying bad action params.
	}

	// @todo: Should this return status 400? Bad action param to a regular wiki page doesn't...
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
 * In the throw, specify the Flow permission, e.g. 'flow-edit-post', or the core permission, e.g. 'edit'
 */
class PermissionException extends PermissionsError {
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
