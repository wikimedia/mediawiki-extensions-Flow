<?php

use Flow\Import\Importer;
use Flow\Import\LiquidThreadsApi\ImportSource as LiquidThreadsApiImportSource;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertLqt extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts LiquidThreads data to Flow data";
		$this->addArg( 'api', 'URL to the API of the wiki to import from', true );
		$this->addArg( 'srcpage', 'Page name of the remote page to import from', true );
		$this->addArg( 'dstpage', 'Page name of the local page to import to', true );
	}

	public function execute() {
		$apiUrl = $this->getArg( 0 );
		$srcPageName = $this->getArg( 1 );
		$dstPageName = $this->getArg( 2 );

		$importer = Flow\Container::get( 'importer' );
		$source = new LiquidThreadsApiImportSource( $apiUrl, $srcPageName );
		$title = Title::newFromText( $dstPageName );

		$importer->import( $source, $title );
	}
}

$maintClass = "ConvertLqt";
require_once ( RUN_MAINTENANCE_IF_MAIN );
