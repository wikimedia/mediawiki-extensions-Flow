<?php

namespace Flow\Import;

use DeferredUpdates;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use IP;
use MWCryptRand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use SplQueue;
use Title;
use UIDGenerator;
use User;

class PageImportState {
	/**
	 * @var LoggerInterface
	 */
	public $logger;

	/**
	 * @var Workflow
	 */
	public $boardWorkflow;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var ReflectionProperty
	 */
	protected $workflowIdProperty;

	/**
	 * @var ReflectionProperty[]
	 */
	protected $postIdProperty;

	/**
	 * @var ReflectionProperty[]
	 */
	protected $revIdProperty;

	/**
	 * @var ReflectionProperty[]
	 */
	protected $lastEditIdProperty;

	/**
	 * @var bool
	 */
	protected $allowUnknownUsernames;

	/**
	 * @var Postprocessor
	 */
	public $postprocessor;

	/**
	 * @var SplQueue
	 */
	protected $deferredQueue;

	public function __construct(
		Workflow $boardWorkflow,
		ManagerGroup $storage,
		ImportSourceStore $sourceStore,
		LoggerInterface $logger,
		BufferedCache $cache,
		DbFactory $dbFactory,
		Postprocessor $postprocessor,
		SplQueue $deferredQueue,
		$allowUnknownUsernames = false
	) {
		$this->storage = $storage;;
		$this->boardWorkflow = $boardWorkflow;
		$this->sourceStore = $sourceStore;
		$this->logger = $logger;
		$this->cache = $cache;
		$this->dbw = $dbFactory->getDB( DB_MASTER );
		$this->postprocessor = $postprocessor;
		$this->deferredQueue = $deferredQueue;
		$this->allowUnknownUsernames = $allowUnknownUsernames;

		// Get our workflow UUID property
		$this->workflowIdProperty = new ReflectionProperty( 'Flow\\Model\\Workflow', 'id' );
		$this->workflowIdProperty->setAccessible( true );

		// Get our revision UUID properties
		$this->postIdProperty = new ReflectionProperty( 'Flow\\Model\\PostRevision', 'postId' );
		$this->postIdProperty->setAccessible( true );
		$this->revIdProperty = new ReflectionProperty( 'Flow\\Model\\AbstractRevision', 'revId' );
		$this->revIdProperty->setAccessible( true );
		$this->lastEditIdProperty = new ReflectionProperty( 'Flow\\Model\\AbstractRevision', 'lastEditId' );
		$this->lastEditIdProperty->setAccessible( true );
	}

	/**
	 * @param object|object[] $object
	 * @param array           $metadata
	 */
	public function put( $object, array $metadata ) {
		$metadata['imported'] = true;
		if ( is_array( $object ) ) {
			$this->storage->multiPut( $object, $metadata );
		} else {
			$this->storage->put( $object, $metadata );
		}
	}

	/**
	 * Gets the given object from storage
	 *
	 * @param  string $type Class name to retrieve
	 * @param  UUID   $id   ID of the object to retrieve
	 * @return Object|false
	 */
	public function get( $type, UUID $id ) {
		return $this->storage->get( $type, $id );
	}

	/**
	 * Gets the top revision of an item by ID
	 *
	 * @param  string $type The type of the object to return (e.g. PostRevision).
	 * @param  UUID   $id   The ID (e.g. post ID, topic ID, etc)
	 * @return object|false The top revision of the requested object, or false if not found.
	 */
	public function getTopRevision( $type, UUID $id ) {
		$result = $this->storage->find(
			$type,
			array( 'rev_type_id' => $id ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		if ( count( $result ) ) {
			return reset( $result );
		} else {
			return false;
		}
	}

	/**
	 * Creates a UUID object representing a given timestamp.
	 *
	 * @param string $timestamp The timestamp to represent, in a wfTimestamp compatible format.
	 * @return UUID
	 */
	public function getTimestampId( $timestamp ) {
		return UUID::create( HistoricalUIDGenerator::historicalTimestampedUID88( $timestamp ) );
	}


	/**
	 * Update the id of the workflow to match the provided timestamp
	 *
	 * @param Workflow $workflow
	 * @param string   $timestamp
	 */
	public function setWorkflowTimestamp( Workflow $workflow, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$this->workflowIdProperty->setValue( $workflow, $uid );
	}

	/**
	 * @var AbstractRevision $summary
	 * @var string           $timestamp
	 */
	public function setRevisionTimestamp( AbstractRevision $revision, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$setRevId = true;

		// We don't set the topic title postId as it was inherited from the workflow.  We only set the
		// postId for first revisions because further revisions inherit it from the parent which was
		// set appropriately.
		if ( $revision instanceof PostRevision && $revision->isFirstRevision() && !$revision->isTopicTitle() ) {
			$this->postIdProperty->setValue( $revision, $uid );
		}

		if ( $setRevId ) {
			if ( $revision->getRevisionId()->equals( $revision->getLastContentEditId() ) ) {
				$this->lastEditIdProperty->setValue( $revision, $uid );
			}
			$this->revIdProperty->setValue( $revision, $uid );
		}
	}

	/**
	 * Records an association between a created object and its source.
	 *
	 * @param  UUID          $objectId UUID representing the object that was created.
	 * @param  IImportObject $object   Output from getObjectKey
	 */
	public function recordAssociation( UUID $objectId, IImportObject $object ) {
		$this->sourceStore->setAssociation( $objectId, $object->getObjectKey() );
	}

	/**
	 * Gets the imported ID for a given object, if any.
	 *
	 * @param IImportObject $object
	 * @return UUID|false
	 */
	public function getImportedId( IImportObject $object ) {
		return $this->sourceStore->getImportedId( $object->getObjectKey() );
	}

	public function createUser( $name ) {
		if ( IP::isIPAddress( $name ) ) {
			return User::newFromName( $name, false );
		}
		$user = User::newFromName( $name );
		if ( !$user ) {
			throw new ImportException( 'Unable to create user: ' . $name );
		}
		if ( $user->getId() == 0 && !$this->allowUnknownUsernames ) {
			throw new ImportException( 'User does not exist: ' . $name );
		}
		return $user;
	}

	public function begin() {
		$this->flushDeferredQueue();
		$this->dbw->begin();
		$this->cache->begin();
	}

	public function commit() {
		$this->dbw->commit();
		$this->cache->commit();
		$this->sourceStore->save();
		$this->flushDeferredQueue();
	}

	public function rollback() {
		$this->dbw->rollback();
		$this->cache->rollback();
		$this->sourceStore->rollback();
		$this->clearDeferredQueue();
		$this->postprocessor->importAborted();
	}

	protected function flushDeferredQueue() {
		while ( !$this->deferredQueue->isEmpty() ) {
			DeferredUpdates::addCallableUpdate( $this->deferredQueue->dequeue() );
		}
		DeferredUpdates::doUpdates();
	}

	protected function clearDeferredQueue() {
		while ( !$this->deferredQueue->isEmpty() ) {
			$this->deferredQueue->dequeue();
		}
	}
}
