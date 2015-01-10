<?php

namespace Flow\Exception;

use Exception;
use OutputPage;
use RequestContext;

/**
 * Flow base exception
 */
class FlowException extends Exception {

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
	 * Overrides Exception getHTML, adding a more human-friendly error message
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
	 */
	public function getPageTitle() {
		return $this->parsePageTitle( 'errorpagetitle' );
	}

	/**
	 * Exception from API/commandline will be handled by Exception::report(),
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
class InvalidInputException extends FlowException {
	protected function getErrorCodeList() {
		return array (
			'invalid-input',
			'missing-revision',
			'revision-comparison',
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
	 * {@inheritDoc}
	 */
	public function getPageTitle() {
		return $this->parsePageTitle( 'nosuchaction' );
	}

	/**
	 * Bad request
	 */
	public function getStatusCode() {
		return 400;
	}

	/**
	 * {@inheritDoc}
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

	/**
	 * Do not log exception resulting from user requesting
	 * disallowed content.
	 */
	function isLoggable() {
		return false;
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
			'missing-topic-title',
			'missing-metadata'
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
 * Category: Parsoid
 */
class NoParsoidException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'process-wikitext' );
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

/**
 * Category: Cross Wiki
 */
class CrossWikiException extends FlowException {}

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
		return array( 'invalid-input' );
	}

	public function getHTML() {
		return wfMessage( 'flow-error-unknown-workflow-id' )->escaped();
	}

	public function getPageTitle() {
		return wfMessage( 'flow-error-unknown-workflow-id-title' )->escaped();
	}
}

/**
 * Specific exception thrown when a page within NS_TOPIC is requested
 * through WorkflowLoaderFactory and it is an invalid uuid
 */
class InvalidTopicUuidException extends InvalidInputException {
	protected function getErrorCodeList() {
		return array( 'invalid-input' );
	}

	public function getHTML() {
		return wfMessage( 'flow-error-invalid-topic-uuid' )->escaped();
	}

	public function getPageTitle() {
		return wfMessage( 'flow-error-invalid-topic-uuid-title' )->escaped();
	}
}

