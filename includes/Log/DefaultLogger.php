<?php

namespace Flow\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class DefaultLogger extends AbstractLogger {

	/**
	 * Sends everything to wfDebugLog for now.
	 *
	 * @param string $logLevel
	 * @param string $message
	 * @param array $context
	 */
	public function log( $logLevel, $message, array $context = array() ) {
		wfDebugLog( 'Flow ', $logLevel . ' ' . $message . ' ' . implode( ',', $context ) );
	}
}
