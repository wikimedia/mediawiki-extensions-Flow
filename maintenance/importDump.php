<?php

use Flow\Container;
use Flow\Dump\Importer;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * FlowBackupReader is mostly copied from core's importDump.php.
 * importFromHandle will call a different Importer, but other than that,
 * this class is mostly the same - it just has some options stripped.
 * I just couldn't extend the original BackupReader class: including that
 * file automatically launches the script.
 */
class FlowBackupReader extends Maintenance {
	protected $dryRun = false;

	public function __construct() {
		parent::__construct();
		$gz = in_array( 'compress.zlib', stream_get_wrappers() )
			? 'ok'
			: '(disabled; requires PHP zlib module)';
		$bz2 = in_array( 'compress.bzip2', stream_get_wrappers() )
			? 'ok'
			: '(disabled; requires PHP bzip2 module)';

		$this->mDescription = <<<TEXT
This script reads pages from an XML file as produced from Flow's
dumpBackup.php, and saves them into the current wiki.

Compressed XML files may be read directly:
  .gz $gz
  .bz2 $bz2
  .7z (if 7za executable is in PATH)

Note that for very large data sets, importDump.php may be slow.
TEXT;
		$this->stderr = fopen( 'php://stderr', 'wt' );
		$this->addOption( 'dry-run', 'Parse dump without actually importing pages' );
		$this->addOption( 'debug', 'Output extra verbose debug information' );
		$this->addArg( 'file', 'Dump file to import [else use stdin]', false );
	}

	public function execute() {
		if ( wfReadOnly() ) {
			$this->error( "Wiki is in read-only mode; you'll need to disable it for import to work.", true );
		}

		$this->dryRun = $this->hasOption( 'dry-run' );

		if ( $this->hasArg() ) {
			$this->importFromFile( $this->getArg() );
		} else {
			$this->importFromStdin();
		}

		$this->output( "Done!\n" );
	}

	protected function importFromFile( $filename ) {
		if ( preg_match( '/\.gz$/', $filename ) ) {
			$filename = 'compress.zlib://' . $filename;
		} elseif ( preg_match( '/\.bz2$/', $filename ) ) {
			$filename = 'compress.bzip2://' . $filename;
		} elseif ( preg_match( '/\.7z$/', $filename ) ) {
			$filename = 'mediawiki.compress.7z://' . $filename;
		}

		$file = fopen( $filename, 'rt' );

		return $this->importFromHandle( $file );
	}

	protected function importFromStdin() {
		$file = fopen( 'php://stdin', 'rt' );
		if ( self::posix_isatty( $file ) ) {
			$this->maybeHelp( true );
		}

		return $this->importFromHandle( $file );
	}

	protected function importFromHandle( $handle ) {
		$source = new ImportStreamSource( $handle );
		$importer = new Importer( $source, $this->getConfig() );

		if ( $this->hasOption( 'debug' ) ) {
			$importer->setDebug( true );
		}

		if ( !$this->dryRun ) {
			$importer->setStorage( Container::get( 'storage' ) );
		}

		return $importer->doImport();
	}
}

$maintClass = 'FlowBackupReader';
require_once RUN_MAINTENANCE_IF_MAIN;
