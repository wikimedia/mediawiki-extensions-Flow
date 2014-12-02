<?php

namespace Flow\Import;

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
use Title;
use UIDGenerator;
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
	/** @var BufferedCache */
	protected $cache;
	/** @var DbFactory */
	protected $dbFactory;
	/** @var bool */
	protected $allowUnknownUsernames;
	/** @var ProcessorGroup **/
	protected $postprocessors;

	public function __construct(
		ManagerGroup $storage,
		WorkflowLoaderFactory $workflowLoaderFactory,
		BufferedCache $cache,
		DbFactory $dbFactory
	) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->cache = $cache;
		$this->dbFactory = $dbFactory;
		$this->postprocessors = new ProcessorGroup;
	}

	public function addPostprocessor( Postprocessor $proc ) {
		$this->postprocessors->add( $proc );
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
	 * @param IImportSource     $source
	 * @param Title             $targetPage
	 * @param ImportSourceStore $sourceStore
	 * @return bool True When the import completes with no failures
	 */
	public function import( IImportSource $source, Title $targetPage, ImportSourceStore $sourceStore ) {
		$operation = new TalkpageImportOperation( $source );
		return $operation->import( new PageImportState(
			$this->workflowLoaderFactory
				->createWorkflowLoader( $targetPage )
				->getWorkflow(),
			$this->storage,
			$sourceStore,
			$this->logger ?: new NullLogger,
			$this->cache,
			$this->dbFactory,
			$this->postprocessors,
			$this->allowUnknownUsernames
		) );
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
class HistoricalUIDGenerator extends UIDGenerator {
	public static function historicalTimestampedUID88( $timestamp, $base = 10 ) {
		static $counter = false;
		if ( $counter === false ) {
			$counter = mt_rand( 0, 256 );
		}

		$time = array(
			// seconds
			wfTimestamp( TS_UNIX, $timestamp ),
			// milliseconds
			mt_rand( 0, 1000 )
		);

		// The UIDGenerator is implemented very specifically to have
		// a single instance, we have to reuse that instance.
		$gen = self::singleton();
		self::rotateNodeId( $gen );
		$binaryUUID = $gen->getTimestampedID88(
			array( $time, ++$counter % 1024 )
		);

		return wfBaseConvert( $binaryUUID, 2, $base );
	}

	/**
	 * Rotate the nodeId to a random one. The stable node is best for
	 * generating "now" uid's on a cluster of servers, but repeated
	 * creation of historical uid's with one or a smaller number of
	 * machines requires use of a random node id.
	 *
	 * @param UIDGenerator $gen
	 */
	protected static function rotateNodeId( UIDGenerator $gen ) {
		// 4 bytes = 32 bits
		$gen->nodeId32 = wfBaseConvert( MWCryptRand::generateHex( 8, true ), 16, 2, 32 );
		// 6 bytes = 48 bits, used for 128bit uid's
		//$gen->nodeId48 = wfBaseConvert( MWCryptRand::generateHex( 12, true ), 16, 2, 48 );
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

	public function __construct(
		Workflow $boardWorkflow,
		ManagerGroup $storage,
		ImportSourceStore $sourceStore,
		LoggerInterface $logger,
		BufferedCache $cache,
		DbFactory $dbFactory,
		Postprocessor $postprocessor,
		$allowUnknownUsernames = false
	) {
		$this->storage = $storage;;
		$this->boardWorkflow = $boardWorkflow;
		$this->sourceStore = $sourceStore;
		$this->logger = $logger;
		$this->cache = $cache;
		$this->dbw = $dbFactory->getDB( DB_MASTER );
		$this->postprocessor = $postprocessor;
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

	/**
	 * Commits the association map.
	 */
	public function saveAssociations() {
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
		$this->dbw->begin();
		$this->cache->begin();
	}

	public function commit() {
		$this->dbw->commit();
		$this->cache->commit();
		$this->sourceStore->save();
		$this->postprocessor->afterTalkpageImported();
	}

	public function rollback() {
		$this->dbw->rollback();
		$this->cache->rollback();
		$this->sourceStore->rollback();
		$this->postprocessor->talkpageImportAborted();
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
	protected $lastModified;

	public function __construct(
		PageImportState $parent,
		Workflow $topicWorkflow,
		PostRevision $topicTitle
	) {
		$this->parent = $parent;
		$this->topicWorkflow = $topicWorkflow;
		$this->topicTitle = $topicTitle;

		$this->workflowModifiedProperty = new ReflectionProperty( 'Flow\\Model\\Workflow', 'lastModified' );
		$this->workflowModifiedProperty->setAccessible( true );

		$this->lastModified = '';
		$this->recordModificationTime( $topicWorkflow->getId() );
	}

	public function getMetadata() {
		return array(
			'workflow' => $this->topicWorkflow,
			'board-workflow' => $this->parent->boardWorkflow,
			'topic-title' => $this->topicTitle,
		);
	}

	/**
	 * Notify the state about a modification action at a given time.
	 *
	 * @param UUID $uuid UUID of the modification revision.
	 */
	public function recordModificationTime( UUID $uuid ) {
		$timestamp = $uuid->getTimestamp();
		$timestamp = wfTimestamp( TS_MW, $timestamp );

		if ( $timestamp > $this->lastModified ) {
			$this->lastModified = $timestamp;
		}
	}

	/**
	 * Saves the last modified timestamp based on calls to recordModificationTime
	 * XXX: Kind of icky; reaching through the parent and doing a second put().
	 */
	public function commitLastModified() {
		$this->workflowModifiedProperty->setValue(
			$this->topicWorkflow,
			$this->lastModified
		);

		$this->parent->put( $this->topicWorkflow, $this->getMetadata() );
	}
}

class TalkpageImportOperation {
	/**
	 * @var IImportSource
	 */
	protected $importSource;

	/**
	 * @param IImportSource $source
	 */
	public function __construct( IImportSource $source ) {
		$this->importSource = $source;
	}

	/**
	 * @param PageImportState $state
	 * @return bool True if import completed successfully
	 */
	public function import( PageImportState $state ) {
		$state->logger->info( 'Importing to ' . $state->boardWorkflow->getArticleTitle()->getPrefixedText() );
		if ( $state->boardWorkflow->isNew() ) {
			$state->put( $state->boardWorkflow, array() );
		}

		$imported = $failed = 0;
		$header = $this->importSource->getHeader();
		if ( $header ) {
			try {
				$state->begin();
				$this->importHeader( $state, $header );
				$state->commit();
				$imported++;
			} catch ( \Exception $e ) {
				$state->rollback();
				\MWExceptionHandler::logException( $e );
				$state->logger->error( 'Failed importing header' );
				$state->logger->error( (string)$e );
				$failed++;
			}
		}

		foreach( $this->importSource->getTopics() as $topic ) {
			try {
				// @todo this may be too large of a chunk for one commit, unsure
				$state->begin();
				$this->importTopic( $state, $topic );
				$state->commit();
				$imported++;
			} catch ( \Exception $e ) {
				$state->rollback();
				\MWExceptionHandler::logException( $e );
				$state->logger->error( 'Failed importing topic' );
				$state->logger->error( (string)$e );
				$failed++;
			}
		}
		$state->logger->info( "Imported $imported items, failed $failed" );

		return $failed === 0;
	}

	/**
	 * @param PageImportState $pageState
	 * @param IImportHeader   $importHeader
	 */
	public function importHeader( PageImportState $pageState, IImportHeader $importHeader ) {
		$pageState->logger->info( 'Importing header' );
		if ( ! $importHeader->getRevisions()->valid() ) {
			$pageState->logger->info( 'no revisions located for header' );
			// No revisions
			return;
		}

		$existingId = $pageState->getImportedId( $importHeader );
		if ( $existingId && $pageState->getTopRevision( 'Header', $existingId ) ) {
			$pageState->logger->info( 'header previously imported' );
			return;
		}

		$revisions = $this->importObjectWithHistory(
			$importHeader,
			function( IObjectRevision $rev ) use ( $pageState ) {
				return Header::create(
					$pageState->boardWorkflow,
					$pageState->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'create-header'
				);
			},
			'edit-header',
			$pageState,
			$pageState->boardWorkflow->getArticleTitle()
		);

		$pageState->put( $revisions, array() );
		$pageState->recordAssociation(
			reset( $revisions )->getCollectionId(),
			$importHeader
		);

		$pageState->logger->info( 'Imported ' . count( $revisions ) . ' revisions for header' );
	}

	/**
	 * @param PageImportState $pageState
	 * @param IImportTopic    $importTopic
	 */
	public function importTopic( PageImportState $pageState, IImportTopic $importTopic ) {
		// $database->begin();
		$topicState = $this->getTopicState( $pageState, $importTopic );

		$summary = $importTopic->getTopicSummary();
		if ( $summary ) {
			$this->importSummary( $topicState, $summary );
		}

		foreach ( $importTopic->getReplies() as $post ) {
			$this->importPost( $topicState, $post, $topicState->topicTitle );
		}

		$topicState->commitLastModified();
		$topicState->parent->saveAssociations();
		$topicId = $topicState->topicWorkflow->getId();
		$pageState->postprocessor->afterTopicImported( $importTopic, $topicId );
		// $database->commit();
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic    $importTopic
	 * @return TopicImportState
	 */
	protected function getTopicState( PageImportState $state, IImportTopic $importTopic ) {
		// Check if it's already been imported
		$topicState = $this->getExistingTopicState( $state, $importTopic );
		if ( $topicState ) {
			$state->logger->info( 'Continuing import to ' . $topicState->topicWorkflow->getArticleTitle()->getPrefixedText() );
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
	 * @param IImportTopic    $importTopic
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
			function( IObjectRevision $rev ) use ( $state, $topicWorkflow ) {
				return PostRevision::create(
					$topicWorkflow,
					$state->createUser( $rev->getAuthor() ),
					$rev->getText()
				);
			},
			'edit-title',
			$state,
			$topicWorkflow->getArticleTitle()
		);

		$topicState = new TopicImportState( $state, $topicWorkflow, end( $titleRevisions ) );
		$topicMetadata = $topicState->getMetadata();

		// TLE must be first, otherwise you get an error importing the Topic Title
		// Flow/includes/Data/Index/BoardHistoryIndex.php:
		// No topic list contains topic XXX, called for revision YYY
		$state->put( $topicListEntry, $topicMetadata );
		// Topic title must be second, because inserting topicWorkflow requires
		// the topic title to already be in place
		$state->put( $titleRevisions, $topicMetadata );
		$state->put( $topicWorkflow, $topicMetadata );

		$state->recordAssociation( $topicWorkflow->getId(), $importTopic );

		$state->logger->info( 'Finished importing topic title with ' . count( $titleRevisions ) . ' revisions' );
		return $topicState;
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic    $importTopic
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
	 * @param IImportSummary   $importSummary
	 */
	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		$state->parent->logger->info( "Importing summary" );
		$existingId = $state->parent->getImportedId( $importSummary );
		if ( $existingId ) {
			$summary = $state->parent->getTopRevision( 'PostSummary', $existingId );
			if ( $summary ) {
				$state->recordModificationTime( $summary->getRevisionId() );
				$state->parent->logger->info( "Summary previously imported" );
				return;
			}
		}

		$revisions = $this->importObjectWithHistory(
			$importSummary,
			function( IObjectRevision $rev ) use ( $state ) {
				return PostSummary::create(
					$state->topicWorkflow->getArticleTitle(),
					$state->topicTitle,
					$state->parent->createUser( $rev->getAuthor() ),
					$rev->getText(),
					'create-topic-summary'
				);
			},
			'edit-topic-summary',
			$state->parent,
			$state->topicWorkflow->getArticleTitle()
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
		);
		$state->parent->put( $revisions, $metadata );
		$state->parent->recordAssociation(
			reset( $revisions )->getCollectionId(), // Summary ID
			$importSummary
		);

		$state->recordModificationTime( end( $revisions )->getRevisionId() );
		$state->parent->logger->info( "Finished importing summary with " . count( $revisions ) . " revisions" );
	}

	/**
	 * @param TopicImportState $state
	 * @param IImportPost      $post
	 * @param PostRevision     $replyTo
	 * @param string           $logPrefix
	 */
	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo,
		$logPrefix = ' '
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
				function( IObjectRevision $rev ) use ( $replyTo, $state ) {
					return $replyTo->reply(
						$state->topicWorkflow,
						$state->parent->createUser( $rev->getAuthor() ),
						$rev->getText()
					);
				},
				'edit-post',
				$state->parent,
				$state->topicWorkflow->getArticleTitle()
			);

			$topRevision = end( $replyRevisions );

			$metadata = array(
				'workflow' => $state->topicWorkflow,
				'board-workflow' => $state->parent->boardWorkflow,
				'topic-title' => $state->topicTitle,
				'reply-to' => $replyTo,
			);

			$state->parent->put( $replyRevisions, $metadata );
			$state->parent->recordAssociation(
				$topRevision->getPostId(),
				$post
			);
			$state->parent->logger->info( $logPrefix . "Finished importing post with " . count( $replyRevisions ) . " revisions" );
		}

		$state->recordModificationTime( $topRevision->getRevisionId() );

		$topicId = $state->topicWorkflow->getId();
		$state->parent->postprocessor->afterPostImported( $post, $topicId, $topRevision->getPostId() );

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $topRevision, $logPrefix . ' ' );
		}
	}

	/**
	 * Imports an object with all its revisions
	 *
	 * @param IRevisionableObject $object              Object to import.
	 * @param callable            $importFirstRevision Function which, given the appropriate import revision, creates the Flow revision.
	 * @param string              $editChangeType      The Flow change type (from FlowActions.php) for each new operation.
	 * @param PageImportState     $state               State of the import operation.
	 * @param Title               $title               Title content is rendered against
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
		$insertObjects = array();
		$revisions = $object->getRevisions();
		$revisions->rewind();

		if ( ! $revisions->valid() ) {
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
		while( $revisions->valid() ) {
			$importRevision = $revisions->current();
			$insertObjects[] = $lastRevision =
				$lastRevision->newNextRevision(
					$state->createUser( $importRevision->getAuthor() ),
					$importRevision->getText(),
					$editChangeType,
					$title
				);

			if ( $importRevision->getTimestamp() < $lastTimestamp ) {
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
