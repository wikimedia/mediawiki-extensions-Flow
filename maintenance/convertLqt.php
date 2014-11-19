<?php

use Flow\Import\FileImportSourceStore;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Psr\Log\NullLogger;

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
		$this->addOption( 'verbose', 'Report on import progress to stdout' );
	}

	public function execute() {
		$logger = $this->getOption( 'verbose' )
			? new MaintenanceDebugLogger( $this )
			: new NullLogger;
		$importer = Flow\Container::get( 'importer' );
		$importer->setLogger( $logger );

		$wikiConverter = new \Flow\Import\LiquidThreadsApi\ConvertWiki(
			$importer,
			$logger,
			new FileImportSourceStore( $this->getOption( 'logfile' ) ),
			new LocalApiBackend(),
			FlowHooks::getOccupationController()->getTalkpageManager()
		);

		$logger->info( "Starting full wiki LQT conversion" );
		$wikiConverter->convert();
	}
}

$maintClass = "ConvertLqt";
require_once ( RUN_MAINTENANCE_IF_MAIN );

