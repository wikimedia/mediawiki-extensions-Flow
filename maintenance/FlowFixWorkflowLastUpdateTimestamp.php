<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use BatchRowUpdate;
use BatchRowWriter;
use Exception;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Exception\DataModelException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\RootPostLoader;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Utils\MWTimestamp;
use MediaWiki\WikiMap\WikiMap;
use RowUpdateGenerator;
use stdClass;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Timestamp\TimestampException;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * @ingroup Maintenance
 */
class FlowFixWorkflowLastUpdateTimestamp extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Fixes any incorrect workflow_last_update_timestamp for topics' );

		$this->setBatchSize( 10 );

		$this->requireExtension( 'Flow' );
	}

	public function execute() {
		global $wgFlowCluster;

		/** @var DbFactory $dbFactory */
		$dbFactory = Container::get( 'db.factory' );
		$storage = Container::get( 'storage' );
		$rootPostLoader = Container::get( 'loader.root_post' );
		$dbr = $dbFactory->getDB( DB_REPLICA );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', $this->getBatchSize() );
		$iterator->setFetchColumns( [ 'workflow_id', 'workflow_type', 'workflow_last_update_timestamp' ] );
		$iterator->addConditions( [ 'workflow_wiki' => WikiMap::getCurrentWikiId() ] );
		$iterator->setCaller( __METHOD__ );

		$writer = new UpdateWorkflowLastUpdateTimestampWriter( $storage, $wgFlowCluster );
		$writer->setCaller( __METHOD__ );

		$updater = new BatchRowUpdate(
			$iterator,
			$writer,
			new UpdateWorkflowLastUpdateTimestampGenerator( $storage, $rootPostLoader, $dbr )
		);
		$updater->setOutput( [ $this, 'output' ] );
		$updater->execute();
	}

	/**
	 * parent::output() is a protected method, only way to access it from a
	 * callback in php5.3 is to make a public function. In 5.4 can replace with
	 * a Closure.
	 *
	 * @param string $out
	 * @param string|null $channel
	 */
	public function output( $out, $channel = null ) {
		parent::output( $out, $channel );
	}
}

class UpdateWorkflowLastUpdateTimestampGenerator implements RowUpdateGenerator {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var RootPostLoader
	 */
	protected $rootPostLoader;

	/**
	 * @var IReadableDatabase
	 */
	protected $db;

	public function __construct( ManagerGroup $storage, RootPostLoader $rootPostLoader, IReadableDatabase $db ) {
		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;
		$this->db = $db;
	}

	/**
	 * @param stdClass $row
	 * @return array
	 * @throws TimestampException
	 * @throws FlowException
	 * @throws InvalidInputException
	 */
	public function update( $row ) {
		$uuid = UUID::create( $row->workflow_id );

		switch ( $row->workflow_type ) {
			case 'discussion':
				$revision = $this->storage->get( 'Header', $uuid );
				break;

			case 'topic':
				// fetch topic (has same id as workflow) via RootPostLoader so
				// all children are populated
				$revision = $this->rootPostLoader->get( $uuid );
				break;

			default:
				throw new FlowException( 'Unknown workflow type: ' . $row->workflow_type );
		}

		if ( !$revision ) {
			return [];
		}

		$timestamp = $this->getUpdateTimestamp( $revision )->getTimestamp( TS_MW );
		if ( $timestamp === wfTimestamp( TS_MW, $row->workflow_last_update_timestamp ) ) {
			// correct update timestamp already, nothing to update
			return [];
		}

		return [ 'workflow_last_update_timestamp' => $this->db->timestamp( $timestamp ) ];
	}

	/**
	 * @param AbstractRevision $revision
	 * @return MWTimestamp
	 * @throws Exception
	 * @throws TimestampException
	 * @throws DataModelException
	 */
	protected function getUpdateTimestamp( AbstractRevision $revision ) {
		$timestamp = $revision->getRevisionId()->getTimestampObj();

		if ( !$revision instanceof PostRevision ) {
			return $timestamp;
		}

		foreach ( $revision->getChildren() as $child ) {
			// go recursive, find timestamp of most recent child post
			$comparison = $this->getUpdateTimestamp( $child );
			$diff = $comparison->diff( $timestamp );

			// invert will be 1 if the diff is a negative time period from
			// child timestamp ($comparison) to $timestamp, which means that
			// $comparison is more recent than our current $timestamp
			if ( $diff->invert ) {
				$timestamp = $comparison;
			}
		}

		return $timestamp;
	}
}

class UpdateWorkflowLastUpdateTimestampWriter extends BatchRowWriter {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @param ManagerGroup $storage
	 * @param string|false $clusterName
	 */
	public function __construct( ManagerGroup $storage, $clusterName = false ) {
		$this->storage = $storage;
		$this->clusterName = $clusterName;
	}

	/**
	 * Overwriting default writer because I want to use Flow storage methods so
	 * the updates also affect cache, not just DB.
	 *
	 * @param array[] $updates
	 */
	public function write( array $updates ) {
		/*
		 * from:
		 * [
		 *     'primaryKey' => [ 'workflow_id' => $id ],
		 *     'updates' => [ 'workflow_last_update_timestamp' => $timestamp ],
		 * ]
		 * to:
		 * [ $id => $timestamp ]
		 */
		$timestamps = array_combine(
			$this->arrayColumn( $this->arrayColumn( $updates, 'primaryKey' ), 'workflow_id' ),
			$this->arrayColumn( $this->arrayColumn( $updates, 'changes' ), 'workflow_last_update_timestamp' )
		);

		/** @var UUID[] $uuids */
		$uuids = array_map( [ UUID::class, 'create' ], array_keys( $timestamps ) );

		/** @var Workflow[] $workflows */
		$workflows = $this->storage->getMulti( 'Workflow', $uuids );
		foreach ( $workflows as $workflow ) {
			$timestamp = $timestamps[$workflow->getId()->getBinary()->__toString()];
			$workflow->updateLastUpdated( UUID::getComparisonUUID( $timestamp ) );
		}

		$this->storage->multiPut( $workflows );

		// prevent memory from filling up
		$this->storage->clear();

		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$lbFactory->waitForReplication( [ 'cluster' => $this->clusterName ] );
	}

	/**
	 * PHP<5.5-compatible array_column alternative.
	 *
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	protected function arrayColumn( array $array, $key ) {
		return array_map( static function ( $item ) use ( $key ) {
			return $item[$key];
		}, $array );
	}
}

$maintClass = FlowFixWorkflowLastUpdateTimestamp::class;
require_once RUN_MAINTENANCE_IF_MAIN;
