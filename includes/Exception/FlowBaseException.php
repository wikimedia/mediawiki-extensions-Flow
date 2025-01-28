<?php
declare( strict_types = 1 );

namespace Flow\Exception;

use Throwable;
use Wikimedia\NormalizedException\NormalizedException;

/**
 * Workaround for a PHP 7.4 issue (T384905).
 *
 * This class exists because, if {@link FlowException} directly extends {@link NormalizedException},
 * PHP will complain that {@link FlowException::__construct()} has a signature
 * that is not compatible with {@link FlowException::normalizedConstructor()}.
 * The error only exists in PHP 7.4 and earlier (PHP 8 correctly recognizes that
 * incompatible constructor signatures are not problematic, because constructors
 * are not called as methods on an existing object instance of an unknown subclass),
 * so this class can be removed once we only support PHP 8.
 *
 * @license GPL-2.0-or-later
 */
class FlowBaseException extends NormalizedException {

	public function __construct(
		string $normalizedMessage,
		array $messageContext = [],
		int $code = 0,
		?Throwable $previous = null
	) {
		parent::__construct( $normalizedMessage, $messageContext, $code, $previous );
	}

}
