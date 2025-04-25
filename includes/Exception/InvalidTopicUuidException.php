<?php

namespace Flow\Exception;

use MediaWiki\Exception\ErrorPageError;
use MediaWiki\Title\Title;

/**
 * Specific exception thrown when a page within NS_TOPIC is requested
 * through WorkflowLoaderFactory and it is an invalid uuid
 */
class InvalidTopicUuidException extends InvalidInputException {

	private ?string $invalidTopic;

	public function __construct( string $message = '', string $code = 'default', ?string $invalidTopic = null ) {
		parent::__construct( $message, $code );
		$this->invalidTopic = $invalidTopic;
	}

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

	public function report( $action = ErrorPageError::SEND_OUTPUT ) {
		parent::report( $action );

		if ( $this->invalidTopic !== null && str_ends_with( $this->invalidTopic, '/' ) ) {
			global $wgOut;
			$maybeCorrectTitle = Title::newFromText( rtrim( $this->invalidTopic, '/' ), NS_TOPIC );
			$wgOut->addHTML( wfMessage( 'flow-invalid-topic-did-you-mean', $maybeCorrectTitle->getPrefixedText() ) );
		}
	}
}
