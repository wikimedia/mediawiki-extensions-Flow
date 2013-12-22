<?php

namespace Flow\Exception;

use MWException;
use MWExceptionHandler;
use IcontextSource;
use Flow\Templating;

/**
 * Handles all kinds of flow exceptions, the purpose is not to show exception trace
 * to end users.  It works by grouping flow exceptions into different categories,
 * then we can add different handling for the different exception types, we also
 * use the error code to present localized error message to users
 */
class FlowExceptionHandling {
	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @var IContextSource
	 */
	protected $requestContext;

	/**
	 * @param Templating
	 * @param IContextSource
	 */
	public function __construct( Templating $templating, IContextSource $requestContext ) {
		$this->templating = $templating;
		$this->requestContext = $requestContext;
	}

	/**
	 * @param FlowException
	 */
	public function handle( FlowException $e ) {
		// Handle any exception specific error
		$e->handleError();
		$this->showError( $e );
	}

	/**
	 * @param FlowException
	 */
	public function showError( FlowException $e ) {
		$this->requestContext->getOutput()->addModuleStyles(
			array( 'mediawiki.ui', 'ext.flow.base' )
		);
		$this->requestContext->getOutput()->addHTML(
			$this->templating->render(
				'flow:flow-error.html.php',
				array( 'message' => $e->getErrorMessage() )
			)
		);
	}
}

/**
 * Flow base exception
 */
class FlowException extends MWException {

	protected $code;

	/**
	 * @param string - The message from exception, used for debugging error
	 * @param string - The error code used to display error message
	 */
	public function __construct( $message, $code = 'default' ) {
		parent::__construct( $message );
		$this->code = $code;
	}

	/**
	 * Get the error message to be displayed in user interface
	 */
	public function getErrorMessage() {
		$list = $this->getErrorCodeList();
		if ( !in_array( $this->code, $list ) ) {
			$this->code = 'default';
		}
		return wfMessage( 'flow-error-' . $this->code )->escaped();
	}

	/**
	 * Error code list for this exception
	 */
	protected function getErrorCodeList() {
		return array ( 'default' );
	}

	/**
	 * Log the exception by defalut, child classes can overwrite this action
	 */
	public function handleError() {
		wfDebugLog( get_class( $this ), $this->getCode() . ': ' . $this->getTraceAsString() );
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
}

/**
 * Category: invalid action exception
 */
class InvalidActionException extends FlowException {
	protected function getErrorCodeList() {
		return array ( 'invalid-action'	);
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
