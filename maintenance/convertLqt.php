<?php

use Flow\Import\FileImportSourceStore;
use Flow\Import\Importer;
use Flow\Import\NullImportSourceStore;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Flow\Import\LiquidThreadsApi\RemoteApiBackend;
use Flow\Import\LiquidThreadsApi\ImportSource as LiquidThreadsApiImportSource;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertLqt extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts LiquidThreads data to Flow data";
		$this->addArg( 'dstpage', 'Page name of the local page to import to', true );
		$this->addOption( 'srcpage', 'Page name of the remote page to import from. If not specified defaults to dstpage', false, true );
		$this->addOption( 'remoteapi', 'Remote API URL to read from', false, true );
		$this->addOption( 'logfile', 'File to read and store associations between imported items and their sources', false, true );
		$this->addOption( 'verbose', 'Report on import progress to stdout' );
		$this->addOption( 'allowunknownusernames', 'Allow import of usernames that do not exist on this wiki.  DO NOT USE IN PRODUCTION. This simplifies testing imports of production data to a test wiki' );
	}

	public function execute() {
		$dstPageName = $srcPageName = $this->getArg( 0 );

		if ( $this->hasOption( 'srcpage' ) ) {
			$srcPageName = $this->getOption( 'srcpage' );
		}

		if ( $this->hasOption( 'remoteapi' ) ) {
			$api = new RemoteApiBackend( $this->getOption( 'remoteapi' ) );
		} else {
			$api = new LocalApiBackend;
		}

		$importer = Flow\Container::get( 'importer' );
		if ( $this->getOption( 'verbose' ) ) {
			$logger = new MaintenanceDebugLogger( $this );
			$importer->setLogger( $logger );
			$logger->info( "Starting LQT import from $srcPageName to $dstPageName" );
		}
		if ( $this->getOption( 'allowunknownusernames' ) ) {
			$importer->setAllowUnknownUsernames( true );
		}
		$source = new LiquidThreadsApiImportSource( $api, $srcPageName );
		$title = Title::newFromText( $dstPageName );

		if ( $this->hasOption( 'logfile' ) ) {
			$filename = $this->getOption( 'logfile' );
			$sourceStore = new FileImportSourceStore( $filename );
		} else {
			$sourceStore = new NullImportSourceStore;
		}

		$importer->import( $source, $title, $sourceStore );

		$sourceStore->save();
	}
}

$maintClass = "ConvertLqt";
require_once ( RUN_MAINTENANCE_IF_MAIN );
