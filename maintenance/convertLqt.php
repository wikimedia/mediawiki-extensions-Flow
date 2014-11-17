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
		$this->addArg( 'srcpage', 'Page name of the remote page to import from', true );
		$this->addArg( 'dstpage', 'Page name of the local page to import to', true );
		$this->addOption( 'remote-api', 'Remote API URL to read from' );
		$this->addOption( 'logfile', 'File to read and store associations between imported items and their sources', false, true );
		$this->addOption( 'verbose', 'Report on import progress to stdout' );
		$this->addOption( 'allowunknownusernames', 'Allow import of usernames that do not exist on this wiki.  DO NOT USE IN PRODUCTION. This simplifies testing imports of production data to a test wiki' );
	}

	public function execute() {
		$srcPageName = $this->getArg( 0 );
		$dstPageName = $this->getArg( 1 );

		if ( $this->getOption( 'remote-api' ) ) {
			$apiUrl = $this->getOption( 'remote-api' );
			$api = new RemoteApiBackend( $apiUrl );
		} else {
			$api = new LocalApiBackend;
		}

		$importer = Flow\Container::get( 'importer' );
		if ( $this->getOption( 'verbose' ) ) {
			$importer->setLogger( new MaintenanceDebugLogger( $this ) );
		}
		if ( $this->getOption( 'allowunknownusernames' ) ) {
			$importer->setAllowUnknownUsernames( true );
		}
		$source = new LiquidThreadsApiImportSource( $api, $srcPageName );
		$title = Title::newFromText( $dstPageName );

		if ( $this->getOption( 'logfile' ) ) {
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
