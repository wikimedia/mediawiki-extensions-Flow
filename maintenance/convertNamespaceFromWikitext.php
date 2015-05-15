<?php

use Flow\Utils\NamespaceIterator;
use Psr\Log\NullLogger;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Converts a single namespace from wikitext talk pages to flow talk pages.  Does not
 * modify liquid threads pages it comes across, use convertLqt.php for that.  Does not
 * modify sub-pages. Does not modify LiquidThreads enabled pages.
 */
class ConvertNamespaceFromWikitext extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a single namespace of wikitext talk pages to Flow";
		$this->addArg( 'namespace', 'Name of the namespace to convert' );
	}

	public function execute() {
		global $wgLang, $wgParser;

		$provided = $this->getArg( 0 );
		$namespace = $wgLang->getNsIndex( $provided );
		if ( !$namespace ) {
			$this->error( "Invalid namespace provided: $provided" );
			return;
		}

		// @todo send to prod logger?
		$logger = new MaintenanceDebugLogger( $this );

		$dbw = wfGetDB( DB_MASTER );
		$converter = new \Flow\Import\Converter(
			$dbw,
			Flow\Container::get( 'importer' ),
			$logger,
			FlowHooks::getOccupationController()->getTalkpageManager(),
			new Flow\Import\Wikitext\ConversionStrategy(
				$wgParser,
				new Flow\Import\NullImportSourceStore(),
				$logger
			)
		);

		$namespaceName = $wgLang->getNsText( $namespace );
		$logger->info( "Starting conversion of $namespaceName namespace" );

		// Iterate over all existing pages of the namespace.
		$it = new NamespaceIterator( $dbw, $namespace );
		// NamespaceIterator is an IteratorAggregate. Get an Iterator
		// so we can wrap that.
		$it = $it->getIterator();


		$converter->convertAll( $it );
	}
}

$maintClass = "ConvertNamespaceFromWikitext";
require_once ( RUN_MAINTENANCE_IF_MAIN );
