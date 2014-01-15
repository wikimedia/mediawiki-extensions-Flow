<?php

class MaintenanceDebugLogger extends \Psr\Log\AbstractLogger {
	/**
	 * @var Maintenance
	 */
	protected $maintenance;

	public function __construct( Maintenance $maintenance ) {
		$this->maintenance = $maintenance;
	}

	public function log( $level, $message, array $context = array() ) {
		$this->maintenance->outputChanneled( $message );
	}
}
