<?php

namespace Flow\Maintenance;

use AppendIterator;
use Flow\Container;
use Flow\Import\Converter;
use Flow\Import\LiquidThreadsApi\ConversionStrategy;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Flow\Import\SourceStore\FileImportSourceStore;
use Flow\Import\SourceStore\FlowRevisionsDb;
use Flow\OccupationController;
use Flow\Utils\NamespaceIterator;
use Flow\Utils\PagesWithPropertyIterator;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Wikimedia\Rdbms\IReadableDatabase;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Converts all LiquidThreads pages on a wiki to Flow. When using the logfile
 * option this process is idempotent.It may be run many times and will only import
 * one copy of each item.
 */
class ConvertAllLqtPages extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Converts LiquidThreads data to Flow data" );
		$this->addOption( 'logfile', 'File to read and store associations between imported items ' .
			'and their sources. This is required for the import to be idempotent.', false, true );
		$this->addOption( 'force-recovery-conversion', 'If a previous logfile was lost, this ' .
			'option can be set to attempt to map threads to topics that have already been ' .
			'imported to prevent doubles.' );
		$this->addOption( 'debug', 'Include debug information with progress report' );
		$this->requireExtension( 'Flow' );
	}

	public function execute() {
		$logfile = $this->getOption( 'logfile' );
		if ( $logfile ) {
			$sourceStore = new FileImportSourceStore( $logfile );
		} elseif ( $this->getOption( 'force-recovery-conversion' ) ) {
			// fallback: if we don't have a sourcestore to go on, at least look
			// at DB to figure out what's already imported...
			$dbr = Container::get( 'db.factory' )->getDB( DB_REPLICA );
			$sourceStore = new FlowRevisionsDb( $dbr );
		} else {
			$this->error( 'Param logfile or force-recovery-conversion required!' );
			$this->maybeHelp( true );
			die( 1 );
		}

		$logger = new MaintenanceDebugLogger( $this );
		if ( $this->getOption( 'debug' ) ) {
			$logger->setMaximumLevel( LogLevel::DEBUG );
		} else {
			$logger->setMaximumLevel( LogLevel::INFO );
		}

		$importer = Container::get( 'importer' );
		/** @var OccupationController $occupationController */
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$talkpageManagerUser = $occupationController->getTalkpageManager();

		$dbw = $this->getPrimaryDB();
		$strategy = new ConversionStrategy(
			$dbw,
			$sourceStore,
			new LocalApiBackend( $talkpageManagerUser ),
			Container::get( 'url_generator' ),
			$talkpageManagerUser,
			Container::get( 'controller.notification' )
		);

		$converter = new Converter(
			$dbw,
			$importer,
			$logger,
			$talkpageManagerUser,
			$strategy
		);

		$titles = $this->buildIterator( $logger, $dbw );

		$logger->info( "Starting full wiki LQT conversion of all LiquidThreads pages" );
		$converter->convertAll( $titles );
		$logger->info( "Finished conversion" );
	}

	/**
	 * @param AbstractLogger $logger
	 * @param IReadableDatabase $db
	 *
	 * @return AppendIterator
	 */
	private function buildIterator( $logger, $db ) {
		global $wgLqtTalkPages;

		$iterator = new AppendIterator();

		$logger->info( "Considering for conversion: pages with the 'use-liquid-threads' property" );
		$withProperty = new PagesWithPropertyIterator( $db, 'use-liquid-threads' );
		$iterator->append( $withProperty->getIterator() );

		if ( $wgLqtTalkPages ) {
			foreach ( $this->getServiceContainer()->getNamespaceInfo()->getTalkNamespaces() as $ns ) {
				$logger->info( "Considering for conversion: pages in namespace $ns" );
				$it = new NamespaceIterator( $db, $ns );
				$iterator->append( $it->getIterator() );
			}
		}

		return $iterator;
	}
}

$maintClass = ConvertAllLqtPages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
