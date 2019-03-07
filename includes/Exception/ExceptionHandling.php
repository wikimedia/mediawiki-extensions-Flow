<?php

namespace Flow\Exception;

use MWException;
use OutputPage;
use RequestContext;

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
	 * @param string $message The message from exception, used for debugging error
	 * @param string $code The error code used to display error message
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
	 * @param OutputPage $output
	 */
	public function setOutput( OutputPage $output ) {
		$this->output = $output;
	}

	/**
	 * Get the message key for the localized error message
	 * @return string
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
	 * @return string[]
	 */
	protected function getErrorCodeList() {
		// flow-error-default
		return [ 'default' ];
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
		$rc = new RequestContext();
		$output = $rc->getOutput();
		$output->showErrorPage( $this->getPageTitle(), $this->getErrorCode() );
		$output->addHTML( parent::getHTML() );
		return $output->getHTML();
	}

	/**
	 * Helper function for msg function in the convenience of a default callback
	 * @param string $key
	 * @return string
	 */
	public function parsePageTitle( $key ) {
		global $wgSitename;
		return $this->msg( $key, "$1 - $wgSitename", $this->msg( 'internalerror', 'Internal error' ) );
	}

	/**
	 * Error page title
	 * @return string
	 */
	public function getPageTitle() {
		return $this->parsePageTitle( 'errorpagetitle' );
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
	 * @return int
	 */
	public function getStatusCode() {
		return 500;
	}
}

/**
 * Category: invalid input exception
 *
 * This is not logged, and must *only* be used when the error is caused by invalid end-user
 * input.  The same applies to the subclasses.
 *
 * If it is a logic error (including a missing or incorrect parameter not directly caused
 * by user input), or another kind of failure, another (loggable) exception must be used.
 */
class InvalidInputException extends FlowException {
	protected function getErrorCodeList() {
		// Comments are i18n messages, for grepping
		return [
			'invalid-input', // flow-error-invalid-input
			'missing-revision', // flow-error-missing-revision
			'revision-comparison', // flow-error-revision-comparison
			'invalid-workflow', // flow-error-invalid-workflow
		];
	}

	/**
	 * Bad request
	 * @return int
	 */
	public function getStatusCode() {
		return 400;
	}

	/**
	 * Do not log exception resulting from input error
	 * @return bool
	 */
	public function isLoggable() {
		return false;
	}
}

/**
 * This is not logged, and must *only* be used for reference
 * errors caused by invalid (unprocessable) end-user input
 */
class InvalidReferenceException extends InvalidInputException {
}

/**
 * Category: invalid action exception
 */
class InvalidActionException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-invalid-action
		return [ 'invalid-action' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getPageTitle() {
		return $this->parsePageTitle( 'nosuchaction' );
	}

	/**
	 * Bad request
	 * @return int
	 */
	public function getStatusCode() {
		return 400;
	}

	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		// we only want a nice error message here, no stack trace
		$rc = new RequestContext();
		$output = $rc->getOutput();
		$output->showErrorPage( $this->getPageTitle(), $this->getErrorCode() );
		return $output->getHTML();
	}

	/**
	 * Do not log exception resulting from input error
	 * @return bool
	 */
	public function isLoggable() {
		return false;
	}
}

/**
 * Category: commit failure exception
 */
class FailCommitException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-fail-commit
		return [ 'fail-commit' ];
	}
}

/**
 * Category: permission related exception
 */
class PermissionException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-insufficient-permission
		return [ 'insufficient-permission' ];
	}

	/**
	 * Do not log exception resulting from user requesting
	 * disallowed content.
	 * @return bool
	 */
	public function isLoggable() {
		return false;
	}
}

/**
 * Category: invalid data exception
 */
class InvalidDataException extends FlowException {
	protected function getErrorCodeList() {
		return [
			'invalid-title', // flow-error-invalid-title
			'fail-load-data', // flow-error-fail-load-data
			'fail-load-history', // flow-error-fail-load-history
			'fail-search', // flow-error-fail-search
			'missing-topic-title', // flow-error-missing-topic-title
			'missing-metadata', // flow-error-missing-metadata
			'different-page', // flow-error-different-page
		];
	}
}

/**
 * Category: data model processing exception
 */
class DataModelException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-process-data
		return [ 'process-data' ];
	}
}

/**
 * Category: data persistency exception
 */
class DataPersistenceException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-process-data
		return [ 'process-data' ];
	}
}

/**
 * Category: Parsoid
 */
class NoParserException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-process-wikitext
		return [ 'process-wikitext' ];
	}
}

/**
 * Category: wikitext/html conversion exception
 */
class WikitextException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-process-wikitext
		return [ 'process-wikitext' ];
	}
}

/**
 * Category: Data Index
 */
class NoIndexException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-no-index
		return [ 'no-index' ];
	}
}

/**
 * Category: Cross Wiki
 */
class CrossWikiException extends FlowException {
}

/**
 * Category: Template helper
 */
class WrongNumberArgumentsException extends FlowException {
	/**
	 * @param array $args
	 * @param string $minExpected
	 * @param string|null $maxExpected
	 */
	public function __construct( array $args, $minExpected, $maxExpected = null ) {
		$count = count( $args );
		if ( $maxExpected === null ) {
			parent::__construct( "Expected $minExpected arguments but received $count" );
		} else {
			parent::__construct( "Expected between $minExpected and $maxExpected arguments but received $count" );
		}
	}
}

/**
 * Specific exception thrown when a workflow is requested by id through
 * WorkflowLoaderFactory and it does not exist.
 */
class UnknownWorkflowIdException extends InvalidInputException {
	protected function getErrorCodeList() {
		// flow-error-invalid-input
		return [ 'invalid-input' ];
	}

	public function getPageTitle() {
		return wfMessage( 'flow-error-unknown-workflow-id-title' )->text();
	}
}

/**
 * Specific exception thrown when a page within NS_TOPIC is requested
 * through WorkflowLoaderFactory and it is an invalid uuid
 */
class InvalidTopicUuidException extends InvalidInputException {
	protected function getErrorCodeList() {
		// flow-error-invalid-input
		return [ 'invalid-input' ];
	}

	public function getHTML() {
		return wfMessage( 'flow-error-invalid-topic-uuid' )->escaped();
	}

	public function getPageTitle() {
		return wfMessage( 'flow-error-invalid-topic-uuid-title' )->text();
	}
}

/**
 * Exception for missing or invalid parameters to method calls, when not traced directly to
 * user input.
 *
 * This deliberately does not extend InvalidInputException, and must be loggable
 */
class InvalidParameterException extends FlowException {
	public function __construct( $message ) {
		parent::__construct( $message, 'invalid-parameter' );
	}

	protected function getErrorCodeList() {
		// flow-error-invalid-parameter
		return [ 'invalid-parameter' ];
	}
}
