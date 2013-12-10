<?php

namespace Flow\Exception;

use MWException;
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
		// Log non-localized error message for debuggin purpose
		wfDebugLog( get_class( $e ), $e->getCode() . ': ' . $e->getTraceAsString() );
		// Handle any exception specific error handling request
		$e->handleError();
		$this->showError( $e );
	}

	/**
	 * @param FlowException
	 */
	public function showError( FlowException $e ) {
		$this->requestContext->getOutput()->addModuleStyles( array( 'mediawiki.ui', 'ext.flow.base' ) );
		$this->requestContext->getOutput()->addHTML(
			$this->templating->render( 'flow:flow-error.html.php', array( 'message' => $e->getErrorMessage() ) )
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

	public function getErrorMessage() {
		$list = $this->getErrorCodeList();
		if ( !in_array( $this->code, $list ) ) {
			$this->code = 'default';
		}
		return wfMessage( 'flow-error-' . $this->code )->escaped();
	}

	protected function getErrorCodeList() {
		return array (
			'default'
		);	
	}

	// Do nothing by default
	protected function handleError() {
		
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
		return array (
			'invalid-action'	
		);
	}

}

/**
 * Category: commit failure exception
 */
class FailCommitException extends FlowException {

	protected function getErrorCodeList() {
		return array (
			'fail-commit'
		);
	}
	
}

/**
 * Category: permission related exception
 */
class PermissionException extends FlowException {

	protected function getErrorCodeList() {
		return array (
			'insufficient-permission'	
		);
	}

}

/**
 * Category: invalid data exception
 */
class InvalidDataException extends FlowException {
	
	protected function getErrorCodeList() {
		return array (
			'invalid-title',
			'fail-load',
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
		return array (
			'process-data'
		);
	}	

}

/**
 * Category: data persistency exception
 */
class DataPersistenceException extends FlowException {

	protected function getErrorCodeList() {
		return array (
			'process-data'
		);
	}
	
}

/**
 * Category: wikitext/html conversion exception
 */
class WikitextException extends FlowException {

	protected function getErrorCodeList() {
		return array (
			'process-wikitext'	
		);
	}

}
