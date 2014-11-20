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
		$this->addOption( 'verbose', 'Report on import progress to stdout' );
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
		$logger = $this->getOption( 'verbose' )
			? new MaintenanceDebugLogger( $this )
			: new NullLogger();

		$dbr = wfGetDB( DB_SLAVE );
		$converter = new \Flow\Import\Converter(
			$dbr,
			Flow\Container::get( 'importer' ),
			$logger,
			FlowHooks::getOccupationController()->getTalkpageManager(),
			new Flow\Import\Wikitext\ConversionStrategy(
				$wgParser,
				new Flow\Import\NullImportSourceStore()
			)
		);

		$namespaceName = $wgLang->getNsText( $namespace );
		$logger->info( "Starting conversion of $namespaceName namespace" );

		// Iterate over all existing pages of the namespace.
		$it = new NamespaceIterator( $dbr, $namespace );
		// NamespaceIterator is an IteratorAggregate. Get an Iterator
		// so we can wrap that.
		$it = $it->getIterator();

		// Filter out sub pages.  This matches the behaviour of
		// $wgFlowOccupyNamespaces, where the main pages get converted
		// to Flow but the sub pages remain wikitext. This is done outside
		// the db query because it varies depending on wiki configuration.
		$it = new CallbackFilterIterator( $it, function( $title ) {
			return ! $title->isSubPage();
		} );

		// if we have liquid threads filter out any pages with that enabled.  They should
		// be converted separately.
		if ( class_exists( 'LqtDispatch' ) ) {
			$it = new CallbackFilterIterator( $it, function( $title ) use ( $logger ) {
				if ( LqtDispatch::isLqtPage( $title ) ) {
					$logger->info( "Skipping LQT enabled page, conversion must be done with convertLqt.php or convertLqtPage.php: $title" );
					return false;
				} else {
					return true;
				}
			} );
		}

		$converter->convert( $it );
	}
}

$maintClass = "ConvertNamespaceFromWikitext";
require_once ( RUN_MAINTENANCE_IF_MAIN );

