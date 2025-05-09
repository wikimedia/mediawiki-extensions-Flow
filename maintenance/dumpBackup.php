<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\Dump\Exporter;
use MediaWiki\Maintenance\BackupDumper;
use WikiExporter;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";
require_once "$IP/maintenance/includes/BackupDumper.php";

class FlowDumpBackup extends BackupDumper {
	/** @var string|null */
	public $workflowStartId = null;
	/** @var string|null */
	public $workflowEndId = null;

	public function __construct( $args = null ) {
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

		$this->addOption( 'full', 'Dump all revisions of every description/post/summary' );
		$this->addOption( 'current', 'Dump only the latest revision of every description/post/summary' );
		$this->addOption( 'pagelist', 'Dump only pages of which the title is included in the file', false, true );

		$this->addOption( 'start', 'Start from page_id n', false, true );
		$this->addOption( 'end', 'Stop before page_id n (exclusive)', false, true );
		$this->addOption( 'boardstart', 'Start from board id n', false, true );
		$this->addOption( 'boardend', 'Stop before board_id n (exclusive)', false, true );
		$this->addOption( 'skip-header', 'Don\'t output the <mediawiki> header' );
		$this->addOption( 'skip-footer', 'Don\'t output the </mediawiki> footer' );

		$this->requireExtension( 'Flow' );

		if ( $args ) {
			$this->loadWithArgv( $args );
			$this->processOptions();
		}
	}

	public function execute() {
		$this->processOptions();

		if ( $this->hasOption( 'full' ) ) {
			$this->dump( WikiExporter::FULL );
		} elseif ( $this->hasOption( 'current' ) ) {
			$this->dump( WikiExporter::CURRENT );
		} else {
			$this->fatalError( 'No valid action specified.' );
		}
	}

	/**
	 * @param int $history WikiExporter::FULL or WikiExporter::CURRENT
	 * @param int $text Unused, but exists for compat with parent
	 */
	public function dump( $history, $text = WikiExporter::TEXT ) {
		# Notice messages will foul up your XML output even if they're
		# relatively harmless.
		if ( ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 'stderr' );
		}

		$db = Container::get( 'db.factory' )->getDB( DB_REPLICA );

		$services = $this->getServiceContainer();
		$exporter = new Exporter(
			$db,
			$services->getCommentStore(),
			$services->getHookContainer(),
			$services->getRevisionStore(),
			$services->getTitleParser(),
			$history,
			WikiExporter::TEXT
		);
		$exporter->setOutputSink( $this->sink );

		if ( !$this->skipHeader ) {
			$exporter->openStream();
		}

		$workflowIterator = $exporter->getWorkflowIterator( $this->pages, $this->startId, $this->endId,
			$this->workflowStartId, $this->workflowEndId );

		$exporter->dump( $workflowIterator );

		if ( !$this->skipFooter ) {
			$exporter->closeStream();
		}

		$this->report( true );
	}

	public function processOptions() {
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
			$this->pages = array_filter(
				$pages,
				static function ( $x ) {
					return $x !== '';
				}
			);
		}

		if ( $this->hasOption( 'start' ) ) {
			$this->startId = intval( $this->getOption( 'start' ) );
		}

		if ( $this->hasOption( 'end' ) ) {
			$this->endId = intval( $this->getOption( 'end' ) );
		}

		if ( $this->hasOption( 'boardstart' ) ) {
			$this->workflowStartId = $this->getOption( 'boardstart' );
		}

		if ( $this->hasOption( 'boardend' ) ) {
			$this->workflowEndId = $this->getOption( 'boardend' );
		}

		$this->skipHeader = $this->hasOption( 'skip-header' );
		$this->skipFooter = $this->hasOption( 'skip-footer' );
	}
}

$maintClass = FlowDumpBackup::class;
require_once RUN_MAINTENANCE_IF_MAIN;
