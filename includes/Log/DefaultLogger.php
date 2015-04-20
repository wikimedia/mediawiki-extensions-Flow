<?php

namespace Flow\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class DefaultLogger extends AbstractLogger {

	/**
	 * Sends everything to wfDebugLog for now.
	 *
	 * @param LogLevel $logLevel
	 * @param string $message
	 * @param array $context
	 */
	protected function log(LogLevel $logLevel, $message, array $context) {
		wfDebugLog( 'Flow ', $logLevel . ' ' . $message . ' ' . implode( ',', $context ) );
	}
}
