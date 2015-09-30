<?php

use Flow\Container;
use Flow\Dump\Exporter;
use Flow\Model\UUID;

$originalDir = getcwd();

$optionsWithArgs = array( 'pagelist', 'start', 'end', 'revstart', 'revend' );

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance'
	: dirname( __FILE__ ) . '/../../../maintenance' );
require_once $maintPath . '/commandLine.inc';
require_once $maintPath . '/backup.inc';

class FlowBackupDumper extends BackupDumper {
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

		$revStartId = $this->revStartId ? UUID::create( $this->revStartId ) : null;
		$revEndId = $this->revEndId ? UUID::create( $this->revEndId ) : null;
		$exporter->dump( $workflowIterator, $revStartId, $revEndId );

		if ( !$this->skipFooter ) {
			$exporter->closeStream();
		}

		$this->report( true );
	}
}

$dumper = new FlowBackupDumper( $argv );

if ( isset( $options['pagelist'] ) ) {
	$olddir = getcwd();
	chdir( $originalDir );
	$pages = file( $options['pagelist'] );
	chdir( $olddir );
	if ( $pages === false ) {
		echo "Unable to open file {$options['pagelist']}\n";
		die( 1 );
	}
	$pages = array_map( 'trim', $pages );
	$dumper->pages = array_filter( $pages, create_function( '$x', 'return $x !== "";' ) );
}

if ( isset( $options['start'] ) ) {
	$dumper->startId = intval( $options['start'] );
}
if ( isset( $options['end'] ) ) {
	$dumper->endId = intval( $options['end'] );
}

if ( isset( $options['revstart'] ) ) {
	$dumper->revStartId = intval( $options['revstart'] );
}
if ( isset( $options['revend'] ) ) {
	$dumper->revEndId = intval( $options['revend'] );
}
$dumper->skipHeader = isset( $options['skip-header'] );
$dumper->skipFooter = isset( $options['skip-footer'] );

if ( isset( $options['full'] ) ) {
	$dumper->dump( WikiExporter::FULL );
} elseif ( isset( $options['current'] ) ) {
	$dumper->dump( WikiExporter::CURRENT );
} else {
	$dumper->progress( <<<ENDS
This script dumps the Flow discussion database into an
XML interchange wrapper format for export or backup.

XML output is sent to stdout; progress reports are sent to stderr.

Usage: php dumpBackup.php <action> [<options>]
Actions:
  --full      Dump all revisions of every description/post/summary.
  --current   Dump only the latest revision of every description/post/summary.
  --pagelist=<file>
              Where <file> is a list of page titles to be dumped
Options:
  --start=n   Start from page_id or log_id n
  --end=n     Stop before page_id or log_id n (exclusive)
  --revstart=n  Start from rev_id n
  --revend=n    Stop before rev_id n (exclusive)
  --skip-header Don't output the <mediawiki> header
  --skip-footer Don't output the </mediawiki> footer

ENDS
	);
}
