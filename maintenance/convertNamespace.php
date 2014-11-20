<?php

use Flow\Utils\NamespaceIterator;
use Psr\Log\NullLogger;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Converts a single namespace from wikitext talk pages to flow talk pages.  Does not
 * modify liquid threads pages it comes across, use convertLqt.php for that.  Does not
 * modify sub-pages.
 */
class ConvertNamespace extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts LiquidThreads data to Flow data";
		$this->addArg( 'namespace', 'Name of the namespace to convert' );
		$this->addOption( 'verbose', 'Report on import progress to stdout' );
	}

	public function execute() {
		global $wgLang;

		$logger = $this->getOption( 'verbose' )
			? new MaintenanceDebugLogger( $this )
			: new NullLogger;

		$provided = $this->getArg( 0 );
		$namespace = $wgLang->getNsIndex( $provided );
		if ( !$namespace ) {
			$this->error( "Invalid namespace provided: $provided" );
			return;
		}
		$namespaceName = $wgLang->getNsText( $namespace );

		$logger->info( "Starting conversion of $namespaceName namespace" );

		$user = FlowHooks::getOccupationController()->getTalkpageManager();
		$converter = new Flow\Import\Wikitext\Converter( $user, $logger );
		$it = new NamespaceIterator( wfGetDB( DB_SLAVE ), $namespace );
		$converter->convert( $it );
	}
}

$maintClass = "ConvertNamespace";
require_once ( RUN_MAINTENANCE_IF_MAIN );

