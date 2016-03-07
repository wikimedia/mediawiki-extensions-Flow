<?php

use Flow\Container;
use Flow\Dump\Exporter;
use Flow\Model\UUID;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance'
	: dirname( __FILE__ ) . '/../../../maintenance' );
require_once $maintPath . '/Maintenance.php';
require_once $maintPath . '/backup.inc';

class FlowDumpBackup extends BackupDumper {
	function __construct( $args = null ) {
		parent::__construct();

		$this->addDescription( <<<TEXT
This script dumps the Flow discussion database into an
XML interchange wrapper format for export.

It can either export only the current revision, or full history.

Although the --full will export all public revisions, non-public revisions
are removed, and the remaining revisions are renormalized to accomodate this.
It is recommended that you keep database backups as well.

XML output is sent to stdout; progress reports are sent to stderr.
TEXT
		);
		$this->stderr = fopen( "php://stderr", "wt" );

		$this->addOption( 'full', 'Dump all revisions of every description/post/summary' );
		$this->addOption( 'current', 'Dump only the latest revision of every description/post/summary' );
		$this->addOption( 'revrange', 'Dump range of revisions specified by revstart and revend parameters' );
		$this->addOption( 'pagelist', 'Dump only pages of which the title is included in the file', false, true );

		$this->addOption( 'start', 'Start from page_id or log_id n', false, true );
		$this->addOption( 'end', 'Stop before page_id or log_id n (exclusive)', false, true );
		$this->addOption( 'revstart', 'Start from rev_id n', false, true );
		$this->addOption( 'revend', 'Stop before rev_id n (exclusive)', false, true );
		$this->addOption( 'skip-header', 'Don\'t output the <mediawiki> header' );
		$this->addOption( 'skip-footer', 'Don\'t output the </mediawiki> footer' );

		if ( $args ) {
			$this->loadWithArgv( $args );
			$this->processOptions();
		}
	}

	function execute() {
		// Stop if Flow not enabled on the wiki
		if ( !class_exists( 'FlowHooks' ) ) {
			echo "Flow isn't enabled on this wiki.\n";
			die( 1 );
		}

		$this->processOptions();

		$textMode = $this->hasOption( 'stub' ) ? WikiExporter::STUB : WikiExporter::TEXT;

		if ( $this->hasOption( 'full' ) ) {
			$this->dump( WikiExporter::FULL, $textMode );
		} elseif ( $this->hasOption( 'current' ) ) {
			$this->dump( WikiExporter::CURRENT, $textMode );
		} elseif ( $this->hasOption( 'revrange' ) ) {
			$this->dump( WikiExporter::RANGE, $textMode );
		} else {
			$this->error( 'No valid action specified.', 1 );
		}
	}

	function dump( $history, $text = Exporter::TEXT ) {
		# Notice messages will foul up your XML output even if they're
		# relatively harmless.
		if ( ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 'stderr' );
		}

		$db = Container::get( 'db.factory' )->getDB( DB_SLAVE );
		$exporter = new Exporter( $db, $history, Exporter::STREAM, Exporter::TEXT );
		$wrapper = new DumpOutput( $this->sink, $this );
		$exporter->setOutputSink( $wrapper );

		if ( !$this->skipHeader ) {
			$exporter->openStream();
		}

		$workflowIterator = $exporter->getWorkflowIterator( $this->pages, $this->startId, $this->endId );

		$revStartId = $history === WikiExporter::RANGE && $this->revStartId ? UUID::create( $this->revStartId ) : null;
		$revEndId = $history === WikiExporter::RANGE && $this->revEndId ? UUID::create( $this->revEndId ) : null;
		$exporter->dump( $workflowIterator, $revStartId, $revEndId );

		if ( !$this->skipFooter ) {
			$exporter->closeStream();
		}

		$this->report( true );
	}

	function processOptions() {
		parent::processOptions();

		// Evaluate options specific to this class
		$this->reporting = !$this->hasOption( 'quiet' );

		if ( $this->hasOption( 'pagelist' ) ) {
			$filename = $this->getOption( 'pagelist' );
			$pages = file( $filename );
			if ( $pages === false ) {
				$this->fatalError( "Unable to open file {$filename}\n" );
			}
			$pages = array_map( 'trim', $pages );
			$this->pages = array_filter( $pages, create_function( '$x', 'return $x !== "";' ) );
		}

		if ( $this->hasOption( 'start' ) ) {
			$this->startId = intval( $this->getOption( 'start' ) );
		}

		if ( $this->hasOption( 'end' ) ) {
			$this->endId = intval( $this->getOption( 'end' ) );
		}

		if ( $this->hasOption( 'revstart' ) ) {
			$this->revStartId = intval( $this->getOption( 'revstart' ) );
		}

		if ( $this->hasOption( 'revend' ) ) {
			$this->revEndId = intval( $this->getOption( 'revend' ) );
		}

		$this->skipHeader = $this->hasOption( 'skip-header' );
		$this->skipFooter = $this->hasOption( 'skip-footer' );
	}
}

$maintClass = 'FlowDumpBackup';
require_once RUN_MAINTENANCE_IF_MAIN;
