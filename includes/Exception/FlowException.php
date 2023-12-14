<?php

namespace Flow\Exception;

use Exception;
use MessageSpecifier;

/**
 * Flow base exception
 */
class FlowException extends Exception implements MessageSpecifier {

	/**
	 * Flow exception error code
	 * @var string
	 */
	protected $code;

	/**
	 * @param string $message The message from exception, used for debugging error
	 * @param string $code The error code used to display error message
	 */
	public function __construct( $message, $code = 'default' ) {
		parent::__construct( $message );
		$this->code = $code;
	}

	/**
	 * Get the message key for the localized error message
	 * @return string
	 */
	final public function getErrorCode() {
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
	 * Implement MessageSpecifier interface to add a more human-friendly error message.
	 * @inheritDoc
	 */
	final public function getKey() {
		return $this->getErrorCode();
	}

	/**
	 * Implement MessageSpecifier interface to add a more human-friendly error message.
	 * @inheritDoc
	 */
	final public function getParams() {
		return [];
	}

}
