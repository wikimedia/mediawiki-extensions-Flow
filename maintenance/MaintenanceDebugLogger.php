<?php

use Psr\Log\LogLevel;

class MaintenanceDebugLogger extends \Psr\Log\AbstractLogger {
	/**
	 * @var Maintenance
	 */
	protected $maintenance;

	/**
	 * @var bool
	 */
	protected $debug = false;

	public function __construct( Maintenance $maintenance ) {
		$this->maintenance = $maintenance;
	}

	public function outputDebug( $shouldOutputDebug ) {
		$this->debug = (bool)$shouldOutputDebug;
	}

	public function log( $level, $message, array $context = array() ) {
		if ( $level === LogLevel::DEBUG && !$this->debug ) {
			return;
		}

		// TS_DB is used as it is a consistent length every time
		$ts = '[' . wfTimestamp( TS_DB ) . ']';
		$this->maintenance->outputChanneled( "$ts $message" );
	}
}
