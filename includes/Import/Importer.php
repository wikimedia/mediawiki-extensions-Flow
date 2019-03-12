<?php

namespace Flow\Import;

use Article;
use DeferredUpdates;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Import\SourceStore\SourceStoreInterface;
use Flow\Import\SourceStore\Exception as ImportSourceStoreException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use Flow\WorkflowLoaderFactory;
use IP;
use MWCryptRand;
use MWTimestamp;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use SplQueue;
use Title;
use User;

/**
 * The import system uses a TalkpageImportOperation class.
 * This class is essentially a factory class that makes the
 * dependency injection less inconvenient for callers.
 */
class Importer {
	/** @var ManagerGroup **/
	protected $storage;
	/** @var WorkflowLoaderFactory **/
	protected $workflowLoaderFactory;
	/** @var LoggerInterface|null */
	protected $logger;
	/** @var DbFactory */
	protected $dbFactory;
	/** @var bool */
	protected $allowUnknownUsernames;
	/** @var ProcessorGroup **/
	protected $postprocessors;
	/** @var SplQueue Callbacks for DeferredUpdate that are queue'd up by the commit process */
	protected $deferredQueue;
	/** @var OccupationController */
	protected $occupationController;

	public function __construct(
		ManagerGroup $storage,
		WorkflowLoaderFactory $workflowLoaderFactory,
		DbFactory $dbFactory,
		SplQueue $deferredQueue,
		OccupationController $occupationController
	) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->dbFactory = $dbFactory;
		$this->postprocessors = new ProcessorGroup;
		$this->deferredQueue = $deferredQueue;
		$this->occupationController = $occupationController;
	}

	public function addPostprocessor( Postprocessor $proc ) {
		$this->postprocessors->add( $proc );
	}

	/**
	 * Returns the ProcessorGroup (calling this triggers all the postprocessors
	 *
	 * @return Postprocessor
	 */
	public function getPostprocessor() {
		return $this->postprocessors;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param bool $allowed When true allow usernames that do not exist on the wiki to be
	 *  stored in the _ip field. *DO*NOT*USE* in any production setting, this is
	 *  to allow for imports from production wiki api's to test machines for
	 *  development purposes.
	 */
	public function setAllowUnknownUsernames( $allowed ) {
		$this->allowUnknownUsernames = (bool)$allowed;
	}

	/**
	 * Imports topics from a data source to a given page.
	 *
	 * @param IImportSource $source
	 * @param Title $targetPage
	 * @param User $user User doing the conversion actions (e.g. initial description,
	 *    wikitext archive edit).  However, actions will be attributed to the original
	 *    user when possible (e.g. the user who did the original LQT reply)
	 * @param SourceStoreInterface $sourceStore
	 * @return bool True When the import completes with no failures
	 */
	public function import(
		IImportSource $source,
		Title $targetPage,
		User $user,
		SourceStoreInterface $sourceStore
	) {
		$operation = new TalkpageImportOperation( $source, $user, $this->occupationController );
		$pageImportState = new PageImportState(
			$this->workflowLoaderFactory
				->createWorkflowLoader( $targetPage )
				->getWorkflow(),
			$this->storage,
			$sourceStore,
			$this->logger ?: new NullLogger,
			$this->dbFactory,
			$this->postprocessors,
			$this->deferredQueue,
			$this->allowUnknownUsernames
		);
		return $operation->import( $pageImportState );
	}
}

/**
 * Modified version of UIDGenerator generates historical timestamped
 * uid's for use when importing older data.
 *
 * DO NOT USE for normal UID generation, this is likely to run into
 * id collisions.
 *
 * The import process needs to identify collision failures reported by
 * the database and re-try importing that item with another generated
 * uid.
 */
class HistoricalUIDGenerator {
	const COUNTER_MAX = 1023; // 2^10 - 1

	public static function historicalTimestampedUID88( $timestamp, $base = 10 ) {
		static $counter = false;
		if ( $counter === false ) {
			$counter = mt_rand( 0, self::COUNTER_MAX );
		}

		// (seconds, milliseconds)
		$time = [ wfTimestamp( TS_UNIX, $timestamp ), mt_rand( 0, 999 ) ];
		++$counter;

		// Take the 46 LSBs of "milliseconds since epoch"
		$id_bin = self::millisecondsSinceEpochBinary( $time );
		// Add a 10 bit counter resulting in 56 bits total
		$id_bin .= str_pad( decbin( $counter % ( self::COUNTER_MAX + 1 ) ), 10, '0', STR_PAD_LEFT );
		// Add the 32 bit node ID resulting in 88 bits total
		$id_bin .= self::newNodeId();
		if ( strlen( $id_bin ) !== 88 ) {
			throw new RuntimeException( "Detected overflow for millisecond timestamp." );
		}

		return \Wikimedia\base_convert( $id_bin, 2, $base );
	}

	/**
	 * @param array $time Array of second and millisecond integers
	 * @return string 46 LSBs of "milliseconds since epoch" in binary (rolls over in 4201)
	 * @throws RuntimeException
	 */
	protected static function millisecondsSinceEpochBinary( array $time ) {
		list( $sec, $msec ) = $time;
		$ts = 1000 * $sec + $msec;
		if ( $ts > 2 ** 52 ) {
			throw new RuntimeException( __METHOD__ .
				': sorry, this function doesn\'t work after the year 144680' );
		}

		return substr( \Wikimedia\base_convert( $ts, 10, 2, 46 ), -46 );
	}

	/**
	 * Rotate the nodeId to a random one. The stable node is best for
	 * generating "now" uid's on a cluster of servers, but repeated
	 * creation of historical uid's with one or a smaller number of
	 * machines requires use of a random node id.
	 *
	 * @return string String of 32 binary digits
	 */
	protected static function newNodeId() {
		// 4 bytes = 32 bits

		return \Wikimedia\base_convert( MWCryptRand::generateHex( 8 ), 16, 2, 32 );
	}
}

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
	 * @var ReflectionProperty
	 */
	protected $postIdProperty;

	/**
	 * @var ReflectionProperty
	 */
	protected $revIdProperty;

	/**
	 * @var ReflectionProperty
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

	/**
	 * @var SourceStoreInterface
	 */
	private $sourceStore;

	/**
	 * @var \Wikimedia\Rdbms\IMaintainableDatabase
	 */
	private $dbw;

	public function __construct(
		Workflow $boardWorkflow,
		ManagerGroup $storage,
		SourceStoreInterface $sourceStore,
		LoggerInterface $logger,
		DbFactory $dbFactory,
		Postprocessor $postprocessor,
		SplQueue $deferredQueue,
		$allowUnknownUsernames = false
	) {
		$this->storage = $storage;
		$this->boardWorkflow = $boardWorkflow;
		$this->sourceStore = $sourceStore;
		$this->logger = $logger;
		$this->dbw = $dbFactory->getDB( DB_MASTER );
		$this->postprocessor = $postprocessor;
		$this->deferredQueue = $deferredQueue;
		$this->allowUnknownUsernames = $allowUnknownUsernames;

		// Get our workflow UUID property
		$this->workflowIdProperty = new ReflectionProperty( Workflow::class, 'id' );
		$this->workflowIdProperty->setAccessible( true );

		// Get our revision UUID properties
		$this->postIdProperty = new ReflectionProperty( PostRevision::class, 'postId' );
		$this->postIdProperty->setAccessible( true );
		$this->revIdProperty = new ReflectionProperty( AbstractRevision::class, 'revId' );
		$this->revIdProperty->setAccessible( true );
		$this->lastEditIdProperty = new ReflectionProperty( AbstractRevision::class, 'lastEditId' );
		$this->lastEditIdProperty->setAccessible( true );
	}

	/**
	 * @param object|object[] $object
	 * @param array $metadata
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
	 * WARNING: Before calling this method, ensure that you follow the rule
	 * given in clearManagerGroup.
	 *
	 * @param string $type Class name to retrieve
	 * @param UUID $id ID of the object to retrieve
	 * @return Object|false
	 */
	public function get( $type, UUID $id ) {
		return $this->storage->get( $type, $id );
	}

	/**
	 * Clears information about which objects are loaded, to avoid memory leaks.
	 * This will also:
	 * * Clear the mapper associated with each ObjectManager that has been used.
	 * * Trigger onAfterClear on any listeners.
	 *
	 * WARNING: You can *NOT* call ->get before calling clearManagerGroup, then ->put
	 * after calling clearManagerGroup, on the same object.  This will cause a
	 * duplicate object to be inserted.
	 */
	public function clearManagerGroup() {
		$this->storage->clear();
	}

	/**
	 * Gets the top revision of an item by ID
	 *
	 * @param string $type The type of the object to return (e.g. PostRevision).
	 * @param UUID $id The ID (e.g. post ID, topic ID, etc)
	 * @return object|false The top revision of the requested object, or false if not found.
	 */
	public function getTopRevision( $type, UUID $id ) {
		$result = $this->storage->find(
			$type,
			[ 'rev_type_id' => $id ],
			[ 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 ]
		);

		if ( is_array( $result ) && count( $result ) ) {
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
	 * @param string $timestamp
	 */
	public function setWorkflowTimestamp( Workflow $workflow, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$this->workflowIdProperty->setValue( $workflow, $uid );
	}

	/**
	 * @param AbstractRevision $revision
	 * @param string $timestamp
	 */
	public function setRevisionTimestamp( AbstractRevision $revision, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		// We don't set the topic title postId as it was inherited from the workflow.  We only set the
		// postId for first revisions because further revisions inherit it from the parent which was
		// set appropriately.
		if ( $revision instanceof PostRevision &&
			$revision->isFirstRevision() && !$revision->isTopicTitle()
		) {
			$this->postIdProperty->setValue( $revision, $uid );
		}

		if ( $revision->getRevisionId()->equals( $revision->getLastContentEditId() ) ) {
			$this->lastEditIdProperty->setValue( $revision, $uid );
		}
		$this->revIdProperty->setValue( $revision, $uid );
	}

	/**
	 * Records an association between a created object and its source.
	 *
	 * @param UUID $objectId UUID representing the object that was created.
	 * @param IImportObject $object Output from getObjectKey
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
		return $this->sourceStore->getImportedId( $object );
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
		$this->dbw->begin( __METHOD__ );
	}

	public function commit() {
		$this->dbw->commit( __METHOD__ );
		$this->sourceStore->save();
		$this->flushDeferredQueue();
	}

	public function rollback() {
		$this->dbw->rollback( __METHOD__ );
		$this->sourceStore->rollback();
		$this->clearDeferredQueue();
		$this->postprocessor->importAborted();
	}

	protected function flushDeferredQueue() {
		while ( !$this->deferredQueue->isEmpty() ) {
			DeferredUpdates::addCallableUpdate(
				$this->deferredQueue->dequeue(),
				DeferredUpdates::PRESEND
			);
			DeferredUpdates::tryOpportunisticExecute();
		}
	}

	protected function clearDeferredQueue() {
		while ( !$this->deferredQueue->isEmpty() ) {
			$this->deferredQueue->dequeue();
		}
	}
}

class TopicImportState {
	/**
	 * @var PageImportState
	 */
	public $parent;

	/**
	 * @var Workflow
	 */
	public $topicWorkflow;

	/**
	 * @var PostRevision
	 */
	public $topicTitle;

	/**
	 * @var string
	 */
	protected $lastUpdated;

	/**
	 * @var ReflectionProperty
	 */
	private $workflowUpdatedProperty;

	public function __construct(
		PageImportState $parent,
		Workflow $topicWorkflow,
		PostRevision $topicTitle
	) {
		$this->parent = $parent;
		$this->topicWorkflow = $topicWorkflow;
		$this->topicTitle = $topicTitle;

		$this->workflowUpdatedProperty = new ReflectionProperty( Workflow::class, 'lastUpdated' );
		$this->workflowUpdatedProperty->setAccessible( true );

		$this->lastUpdated = '';
		$this->recordUpdateTime( $topicWorkflow->getId() );
	}

	public function getMetadata() {
		return [
			'workflow' => $this->topicWorkflow,
			'board-workflow' => $this->parent->boardWorkflow,
			'topic-title' => $this->topicTitle,
		];
	}

	/**
	 * Notify the state about a modification action at a given time.
	 *
	 * @param UUID $uuid UUID of the modification revision.
	 */
	public function recordUpdateTime( UUID $uuid ) {
		$timestamp = $uuid->getTimestamp();
		$timestamp = wfTimestamp( TS_MW, $timestamp );

		if ( $timestamp > $this->lastUpdated ) {
			$this->lastUpdated = $timestamp;
		}
	}

	/**
	 * Saves the last updated timestamp based on calls to recordUpdateTime
	 * XXX: Kind of icky; reaching through the parent and doing a second put().
	 */
	public function commitLastUpdated() {
		$this->workflowUpdatedProperty->setValue(
			$this->topicWorkflow,
			$this->lastUpdated
		);

		$this->parent->put( $this->topicWorkflow, $this->getMetadata() );
	}
}

class TalkpageImportOperation {
	/**
	 * @var IImportSource
	 */
	protected $importSource;

	/** @var User User doing the conversion actions (e.g. initial description, wikitext
	 *    archive edit).  However, actions will be attributed to the original user when
	 *    possible (e.g. the user who did the original LQT reply)
	 */
	protected $user;

	/** @var OccupationController */
	protected $occupationController;

	/**
	 * @param IImportSource $source
	 * @param User $user The import user; this will only be used when there is no
	 *   'original' user
	 * @param OccupationController $occupationController
	 */
	public function __construct(
		IImportSource $source,
		User $user,
		OccupationController $occupationController
	) {
		$this->importSource = $source;
		$this->user = $user;
		$this->occupationController = $occupationController;
	}

	/**
	 * @param PageImportState $state
	 * @return bool True if import completed successfully
	 * @throws ImportSourceStoreException
	 * @throws \Exception
	 */
	public function import( PageImportState $state ) {
		$destinationTitle = $state->boardWorkflow->getArticleTitle();
		$state->logger->info( 'Importing to ' . $destinationTitle->getPrefixedText() );
		$isNew = $state->boardWorkflow->isNew();
		$state->logger->debug( 'Workflow isNew: ' . var_export( $isNew, true ) );
		if ( $isNew ) {
			// Explicitly allow creation of board
			$creationStatus = $this->occupationController->safeAllowCreation(
				$destinationTitle,
				$this->user,
				/* $mustNotExist = */ true
			);
			if ( !$creationStatus->isGood() ) {
				throw new ImportException( "safeAllowCreation failed to allow the import " .
					"destination, with the following error:\n" . $creationStatus->getWikiText() );
			}

			// Makes sure the page exists and a Flow-specific revision has been inserted
			$status = $this->occupationController->ensureFlowRevision(
				new Article( $destinationTitle ),
				$state->boardWorkflow
			);
			$state->logger->debug( 'ensureFlowRevision status isOK: ' .
				var_export( $status->isOK(), true ) );
			$state->logger->debug( 'ensureFlowRevision status isGood: ' .
				var_export( $status->isGood(), true ) );

			if ( $status->isOK() ) {
				$ensureValue = $status->getValue();
				$revision = $ensureValue['revision'];
				$state->logger->debug( 'ensureFlowRevision already-existed: ' .
					var_export( $ensureValue['already-existed'], true ) );
				$revisionId = $revision->getId();
				$pageId = $revision->getTitle()->getArticleID( Title::GAID_FOR_UPDATE );
				$state->logger->debug( "ensureFlowRevision revision ID: $revisionId, page ID: $pageId" );

				$state->put( $state->boardWorkflow, [] );
			} else {
				throw new ImportException( "ensureFlowRevision failed to create the Flow board" );
			}
		}

		$imported = $failed = 0;
		$header = $this->importSource->getHeader();
		try {
			$state->begin();
			$this->importHeader( $state, $header );
			$state->commit();
			$state->postprocessor->afterHeaderImported( $state, $header );
			$imported++;
		} catch ( ImportSourceStoreException $e ) {
			// errors from the source store are more serious and should
			// not just be logged and swallowed.  This may indicate that
			// we are not properly recording progress.
			$state->rollback();
			throw $e;
		} catch ( \Exception $e ) {
			$state->rollback();
			\MWExceptionHandler::logException( $e );
			$state->logger->error( 'Failed importing header: ' . $header->getObjectKey() );
			$state->logger->error( (string)$e );
			$failed++;
		}

		foreach ( $this->importSource->getTopics() as $topic ) {
			try {
				// @todo this may be too large of a chunk for one commit, unsure
				$state->begin();
				$topicState = $this->getTopicState( $state, $topic );
				$this->importTopic( $topicState, $topic );
				$state->commit();
				$state->postprocessor->afterTopicImported( $topicState, $topic );
				$state->clearManagerGroup();

				$imported++;
			} catch ( ImportSourceStoreException $e ) {
				// errors from the source store are more serious and shuld
				// not juts be logged and swallowed.  This may indicate that
				// we are not properly recording progress.
				$state->rollback();
				throw $e;
			} catch ( \Exception $e ) {
				$state->rollback();
				\MWExceptionHandler::logException( $e );
				$state->logger->error( 'Failed importing topic: ' . $topic->getObjectKey() );
				$state->logger->error( (string)$e );
				$failed++;
			}
		}
		$state->logger->info( "Imported $imported items, failed $failed" );

		return $failed === 0;
	}

	/**
	 * @param PageImportState $pageState
	 * @param IImportHeader $importHeader
	 */
	public function importHeader( PageImportState $pageState, IImportHeader $importHeader ) {
		$pageState->logger->info( 'Importing header' );
		if ( !$importHeader->getRevisions()->valid() ) {
			$pageState->logger->info( 'no revisions located for header' );
			// No revisions
			return;
		}

		/*
		 * We don't need $pageState->getImportedId( $importHeader ) here, there
		 * can only be 1 header per workflow and we already know the workflow,
		 * might as well query it from the workflow instead of using the id from
		 * the source store.
		 * reason I prefer not to use source store is that a header import is
		 * incomplete (it doesn't import full history, just the last revision.
		 */
		$existingId = $pageState->boardWorkflow->getId();
		if ( $existingId && $pageState->getTopRevision( 'Header', $existingId ) ) {
			$pageState->logger->info( 'header previously imported' );
			return;
		}

		$revisions = $this->importObjectWithHistory(
			$importHeader,
			function ( IObjectRevision $rev ) use ( $pageState ) {
				return Header::create(
					$pageState->boardWorkflow,
					$pageState->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'wikitext',
					'create-header'
				);
			},
			'edit-header',
			$pageState,
			$pageState->boardWorkflow->getArticleTitle()
		);

		$pageState->put( $revisions, [
			'workflow' => $pageState->boardWorkflow,
		] );
		$pageState->recordAssociation(
			reset( $revisions )->getCollectionId(),
			$importHeader
		);

		$pageState->logger->info( 'Imported ' . count( $revisions ) . ' revisions for header' );
	}

	/**
	 * @param TopicImportState $topicState
	 * @param IImportTopic $importTopic
	 */
	public function importTopic( TopicImportState $topicState, IImportTopic $importTopic ) {
		$summary = $importTopic->getTopicSummary();
		if ( $summary ) {
			$this->importSummary( $topicState, $summary );
		}

		foreach ( $importTopic->getReplies() as $post ) {
			$this->importPost( $topicState, $post, $topicState->topicTitle );
		}

		$topicState->commitLastUpdated();
		$topicState->parent->logger->info( "Finished importing topic" );
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic $importTopic
	 * @return TopicImportState
	 */
	protected function getTopicState( PageImportState $state, IImportTopic $importTopic ) {
		// Check if it's already been imported
		$topicState = $this->getExistingTopicState( $state, $importTopic );
		if ( $topicState ) {
			$state->logger->info( 'Continuing import to ' .
				$topicState->topicWorkflow->getArticleTitle()->getPrefixedText() );
			return $topicState;
		} else {
			return $this->createTopicState( $state, $importTopic );
		}
	}

	protected function getFirstRevision( IRevisionableObject $obj ) {
		$iterator = $obj->getRevisions();
		$iterator->rewind();
		return $iterator->current();
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic $importTopic
	 * @return TopicImportState
	 */
	protected function createTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$state->logger->info( 'Importing new topic' );
		$topicWorkflow = Workflow::create(
			'topic',
			$state->boardWorkflow->getArticleTitle()
		);
		$state->setWorkflowTimestamp(
			$topicWorkflow,
			$this->getFirstRevision( $importTopic )->getTimestamp()
		);

		$topicListEntry = TopicListEntry::create(
			$state->boardWorkflow,
			$topicWorkflow
		);

		$titleRevisions = $this->importObjectWithHistory(
			$importTopic,
			function ( IObjectRevision $rev ) use ( $state, $topicWorkflow ) {
				return PostRevision::createTopicPost(
					$topicWorkflow,
					$state->createUser( $rev->getAuthor() ),
					$rev->getText()
				);
			},
			'edit-title',
			$state,
			$topicWorkflow->getArticleTitle()
		);

		// @phan-suppress-next-line PhanTypeMismatchArgument
		$topicState = new TopicImportState( $state, $topicWorkflow, end( $titleRevisions ) );
		$topicMetadata = $topicState->getMetadata();

		// This should all match the order in TopicListBlock->commit (board/
		// discussion workflow is inserted before this method is called).

		$state->put( $topicWorkflow, $topicMetadata );
		// TLE must be before topic title, otherwise you get an error importing the Topic Title
		// Flow/includes/Data/Index/BoardHistoryIndex.php:
		// No topic list contains topic XXX, called for revision YYY
		$state->put( $topicListEntry, $topicMetadata );
		$state->put( $titleRevisions, $topicMetadata );

		$state->recordAssociation( $topicWorkflow->getId(), $importTopic );

		$state->logger->info( 'Finished importing topic title with ' .
			count( $titleRevisions ) . ' revisions' );
		return $topicState;
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic $importTopic
	 * @return TopicImportState|null
	 */
	protected function getExistingTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$topicId = $state->getImportedId( $importTopic );
		if ( $topicId ) {
			$topicWorkflow = $state->get( 'Workflow', $topicId );
			$topicTitle = $state->getTopRevision( 'PostRevision', $topicId );
			if ( $topicWorkflow instanceof Workflow && $topicTitle instanceof PostRevision ) {
				return new TopicImportState( $state, $topicWorkflow, $topicTitle );
			}
		}

		return null;
	}

	/**
	 * @param TopicImportState $state
	 * @param IImportSummary $importSummary
	 */
	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		$state->parent->logger->info( "Importing summary" );
		$existingId = $state->parent->getImportedId( $importSummary );
		if ( $existingId ) {
			$summary = $state->parent->getTopRevision( 'PostSummary', $existingId );
			if ( $summary ) {
				$state->recordUpdateTime( $summary->getRevisionId() );
				$state->parent->logger->info( "Summary previously imported" );
				return;
			}
		}

		$revisions = $this->importObjectWithHistory(
			$importSummary,
			function ( IObjectRevision $rev ) use ( $state ) {
				return PostSummary::create(
					$state->topicWorkflow->getArticleTitle(),
					$state->topicTitle,
					$state->parent->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'wikitext',
					'create-topic-summary'
				);
			},
			'edit-topic-summary',
			$state->parent,
			$state->topicWorkflow->getArticleTitle()
		);

		$metadata = [
			'workflow' => $state->topicWorkflow,
		];
		$state->parent->put( $revisions, $metadata );
		$state->parent->recordAssociation(
			reset( $revisions )->getCollectionId(), // Summary ID
			$importSummary
		);

		$state->recordUpdateTime( end( $revisions )->getRevisionId() );
		$state->parent->logger->info( "Finished importing summary with " .
			count( $revisions ) . " revisions" );
	}

	/**
	 * @param TopicImportState $state
	 * @param IImportPost $post
	 * @param PostRevision $replyTo
	 * @param string $logPrefix
	 * @suppress PhanTypeMismatchArgument,PhanUndeclaredMethod
	 */
	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo,
		$logPrefix = ''
	) {
		$state->parent->logger->info( $logPrefix . "Importing post" );
		$postId = $state->parent->getImportedId( $post );
		$topRevision = false;
		if ( $postId ) {
			$topRevision = $state->parent->getTopRevision( 'PostRevision', $postId );
		}

		if ( $topRevision ) {
			$state->parent->logger->info( $logPrefix . "Post previously imported" );
		} else {
			$replyRevisions = $this->importObjectWithHistory(
				$post,
				function ( IObjectRevision $rev ) use ( $replyTo, $state ) {
					return $replyTo->reply(
						$state->topicWorkflow,
						$state->parent->createUser( $rev->getAuthor() ),
						$rev->getText(),
						'wikitext'
					);
				},
				'edit-post',
				$state->parent,
				$state->topicWorkflow->getArticleTitle()
			);

			$topRevision = end( $replyRevisions );

			$metadata = [
				'workflow' => $state->topicWorkflow,
				'board-workflow' => $state->parent->boardWorkflow,
				'topic-title' => $state->topicTitle,
				'reply-to' => $replyTo,
			];

			$state->parent->put( $replyRevisions, $metadata );
			$state->parent->recordAssociation(
				$topRevision->getPostId(),
				$post
			);
			$state->parent->logger->info( $logPrefix . "Finished importing post with " .
				count( $replyRevisions ) . " revisions" );
			$state->parent->postprocessor->afterPostImported( $state, $post, $topRevision );
		}

		$state->recordUpdateTime( $topRevision->getRevisionId() );

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $topRevision, $logPrefix . ' ' );
		}
	}

	/**
	 * Imports an object with all its revisions
	 *
	 * @param IRevisionableObject $object Object to import.
	 * @param callable $importFirstRevision Function which, given the appropriate import revision,
	 *   creates the Flow revision.
	 * @param string $editChangeType The Flow change type (from FlowActions.php) for each new operation.
	 * @param PageImportState $state State of the import operation.
	 * @param Title $title Title content is rendered against
	 * @return AbstractRevision[] Objects to insert into the database.
	 * @throws ImportException
	 */
	public function importObjectWithHistory(
		IRevisionableObject $object,
		$importFirstRevision,
		$editChangeType,
		PageImportState $state,
		Title $title
	) {
		$insertObjects = [];
		$revisions = $object->getRevisions();
		$revisions->rewind();

		if ( !$revisions->valid() ) {
			throw new ImportException( "Attempted to import empty history" );
		}

		$importRevision = $revisions->current();
		/** @var AbstractRevision $lastRevision */
		$insertObjects[] = $lastRevision = $importFirstRevision( $importRevision );
		$lastTimestamp = $importRevision->getTimestamp();

		$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
		$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision );
		$state->recordAssociation( $lastRevision->getCollectionId(), $importRevision );

		$revisions->next();
		while ( $revisions->valid() ) {
			$importRevision = $revisions->current();
			$insertObjects[] = $lastRevision =
				$lastRevision->newNextRevision(
					$state->createUser( $importRevision->getAuthor() ),
					$importRevision->getText(),
					'wikitext',
					$editChangeType,
					$title
				);

			$importTimestampObj = new MWTimestamp( $importRevision->getTimestamp() );
			$lastTimestampObj = new MWTimestamp( $lastTimestamp );
			$timeDiff = $lastTimestampObj->diff( $importTimestampObj );
			// If $import - last < 0
			if ( $timeDiff->invert ) {
				throw new ImportException( "Revision listing is not sorted from oldest to newest" );
			}

			$lastTimestamp = $importRevision->getTimestamp();
			$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
			$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision );
			$revisions->next();
		}

		return $insertObjects;
	}
}
