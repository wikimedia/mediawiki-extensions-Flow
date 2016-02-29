<?php

use Flow\Utils\NamespaceIterator;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Converts a single namespace from wikitext talk pages to flow talk pages.  Does not
 * modify LiquidThreads pages it comes across; use convertLqtPagesWithProp.php for that.  Does not
 * modify sub-pages (except talk subpages with a corresponding subject page).
 */
class ConvertNamespaceFromWikitext extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a single namespace of wikitext talk pages to Flow";
		$this->addArg( 'namespace', 'Name of the namespace to convert' );
		$this->addOption(
			'no-convert-templates',
			'Comma-separated list of templates that indicate a page should not be converted',
			false, // not required
			true, // takes argument
			't'
		);
		$this->addOption(
			'archive-pattern',
			'Naming pattern for archive pages; %s for page title, %d for sequence number',
			false, // not required
			true, // takes argument
			'a'
		);
	}

	public function execute() {
		global $wgLang, $wgParser;

		$provided = $this->getArg( 0 );
		$namespace = $wgLang->getNsIndex( $provided );
		if ( !$namespace ) {
			$this->error( "Invalid namespace provided: $provided" );
			return;
		}
		$namespaceName = $wgLang->getNsText( $namespace );
		if ( !MWNamespace::hasSubpages( $namespace ) ) {
			$this->error( "Subpages are not enabled in the $namespaceName namespace." );
			$this->error( "In order to convert this namespace to Flow, you must enable subpages using:" );
			$this->error( "\$wgNamespacesWithSubpages[$namespace] = true;" );
			return;
		}

		$noConvertTemplates = explode( ',', $this->getOption( 'no-convert-templates', '' ) );
		if ( $noConvertTemplates === array( '' ) ) {
			// explode( ',', '' ) returns array( '' )
			$noConvertTemplates = array();
		}
		// Convert to Title objects
		foreach ( $noConvertTemplates as &$template ) {
			$title = Title::newFromText( $template, NS_TEMPLATE );
			if ( !$title ) {
				$this->error( "Invalid template name: $template" );
				return;
			}
			$template = $title;
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
				new Flow\Import\SourceStore\Null(),
				$logger,
				$noConvertTemplates,
				$this->getOption( 'archive-pattern', null )
			)
		);

		$logger->info( "Starting conversion of $namespaceName namespace" );

		// Iterate over all existing pages of the namespace.
		$it = new NamespaceIterator( $dbw, $namespace );
		// NamespaceIterator is an IteratorAggregate. Get an Iterator
		// so we can wrap that.
		$it = $it->getIterator();

		$converter->convertAll( $it );

		$logger->info( "Finished conversion of $namespaceName namespace" );
	}
}

$maintClass = "ConvertNamespaceFromWikitext";
require_once ( RUN_MAINTENANCE_IF_MAIN );
