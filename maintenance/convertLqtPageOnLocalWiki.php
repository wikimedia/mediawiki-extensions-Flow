<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\Import\Converter;
use Flow\Import\LiquidThreadsApi\ConversionStrategy;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Flow\Import\SourceStore\FileImportSourceStore;
use Flow\OccupationController;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Psr\Log\LogLevel;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * This is intended for use both in testing and in production.  It converts a single LQT
 * page on the current wiki to a Flow page on the current wiki, handling archiving.
 */
class ConvertLqtPageOnLocalWiki extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Converts LiquidThreads data to Flow data on the current wiki, using a ConversionStrategy" );
		$this->addOption( 'srcpage', 'Page name of the source page to import from.', true, true );
		$this->addOption( 'logfile', 'File to read and store associations between imported items and their sources', true, true );
		$this->addOption( 'debug', 'Include debug information to progress report' );
		$this->addOption( 'dryrun', 'Show what would be converted, but do not make any changes.' );
		$this->addOption( 'ignoreflowreadonly', 'Ignore $wgFlowReadOnly if set, allowing boards to be created.' );
		$this->addOption( 'convertempty', 'Convert pages even if they have no threads.' );
		$this->addOption( 'insert-ignore', 'Ignore duplicate key insert errors.' );
		$this->requireExtension( 'Flow' );
	}

	public function execute() {
		global $wgFlowReadOnly;

		if ( $wgFlowReadOnly ) {
			if ( $this->getOption( 'ignoreflowreadonly', false ) ) {
				// Make Flow writable for the duration of the script
				$wgFlowReadOnly = false;
			} else {
				$this->error( 'Flow is in read-only mode. Use --ignoreflowreadonly to continue.' );
				return;
			}
		}

		/** @var OccupationController $occupationController */
		$occupationController = MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$talkPageManagerUser = $occupationController->getTalkpageManager();

		$api = new LocalApiBackend( $talkPageManagerUser );

		$importer = Container::get( 'importer' );

		$srcPageName = $this->getOption( 'srcpage' );

		$logFilename = $this->getOption( 'logfile' );
		$sourceStore = new FileImportSourceStore( $logFilename );

		$dbw = $this->getPrimaryDB();

		$logger = new MaintenanceDebugLogger( $this );
		if ( $this->getOption( 'debug' ) ) {
			$logger->setMaximumLevel( LogLevel::DEBUG );
		} else {
			$logger->setMaximumLevel( LogLevel::INFO );
		}

		$strategy = new ConversionStrategy(
			$dbw,
			$sourceStore,
			$api,
			Container::get( 'url_generator' ),
			$talkPageManagerUser,
			Container::get( 'controller.notification' )
		);

		$importer->setLogger( $logger );
		$api->setLogger( $logger );

		$converter = new Converter(
			$dbw,
			$importer,
			$logger,
			$talkPageManagerUser,
			$strategy
		);

		$logger->info( "Starting LQT conversion of page $srcPageName" );

		$srcTitle = Title::newFromText( $srcPageName );
		$dryRun = $this->getOption( 'dryrun', false );
		$convertEmpty = $this->getOption( 'convertempty', false );

		$converter->convertAll( [ $srcTitle ], $dryRun, $convertEmpty );

		$logger->info( "Finished LQT conversion of page $srcPageName" );
	}
}

$maintClass = ConvertLqtPageOnLocalWiki::class;
require_once RUN_MAINTENANCE_IF_MAIN;
