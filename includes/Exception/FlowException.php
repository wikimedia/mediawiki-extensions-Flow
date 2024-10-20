<?php

namespace Flow\Exception;

use Wikimedia\Message\MessageSpecifier;
use Wikimedia\NormalizedException\NormalizedException;

/**
 * Flow base exception
 */
class FlowException extends NormalizedException implements MessageSpecifier {

	/**
	 * Flow exception error code
	 * @var string
	 */
	protected $code;

	/**
	 * @param string $message The message from exception, used for debugging error, with PSR-3 style placeholders.
	 * @param string $code The error code used to display error message
	 * @param array $context Message context, with values for the placeholders.
	 */
	public function __construct( $message, $code = 'default', $context = [] ) {
		parent::__construct( $message, $context );
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
