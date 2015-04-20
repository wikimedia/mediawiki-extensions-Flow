<?php

use Flow\Container;
use Flow\Import\FileImportSourceStore;
use Flow\Import\LiquidThreadsApi\ConversionStrategy;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Flow\Utils\PagesWithPropertyIterator;
use Psr\Log\LogLevel;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Converts all LiquidThreads pages on a wiki to Flow. When using the logfile
 * option this process is idempotent.It may be run many times and will only import
 * one copy of each item.
 */
class ConvertLqt extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts LiquidThreads data to Flow data";
		$this->addOption( 'logfile', 'File to read and store associations between imported items and their sources. This is required for the import to be idempotent.', true, true );
		$this->addOption( 'debug', 'Include debug information with progress report' );
		$this->addOption( 'startId', 'Page id to start importing at (inclusive)' );
		$this->addOption( 'stopId', 'Page id to stop importing at (exclusive)' );
	}

	public function execute() {
		$logger = new MaintenanceDebugLogger( $this );
		if ( $this->getOption( 'debug' ) ) {
			$logger->setMaximumLevel( LogLevel::DEBUG );
		} else {
			$logger->setMaximumLevel( LogLevel::INFO );
		}

		$importer = Flow\Container::get( 'importer' );
		$talkpageManagerUser = FlowHooks::getOccupationController()->getTalkpageManager();

		$dbw = wfGetDB( DB_MASTER );
		$strategy = new ConversionStrategy(
			$dbw,
			new FileImportSourceStore( $this->getOption( 'logfile' ) ),
			new LocalApiBackend( $talkpageManagerUser ),
			Container::get( 'url_generator' ),
			$talkpageManagerUser,
			Container::get( 'controller.notification' )
		);

		$converter = new \Flow\Import\Converter(
			$dbw,
			$importer,
			$logger,
			$talkpageManagerUser,
			$strategy
		);

		$startId = $this->getOption( 'startId' );
		$stopId = $this->getOption( 'stopId' );

		$logger->info( "Starting full wiki LQT conversion" );
		$titles = new PagesWithPropertyIterator( $dbw, 'use-liquid-threads', $startId, $stopId );
		$converter->convertAll( $titles );
	}
}

$maintClass = "ConvertLqt";
require_once ( RUN_MAINTENANCE_IF_MAIN );
