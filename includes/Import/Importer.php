<?php

namespace Flow\Import;

use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use Iterator;
use MWCryptRand;
use ReflectionProperty;
use Title;
use UIDGenerator;

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

	public function __construct(
		ManagerGroup $storage,
		WorkflowLoaderFactory $workflowLoaderFactory
	) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
	}

	/**
	 * Imports topics from a data source to a given page.
	 *
	 * @param IImportSource $source
	 * @param Title $targetPage
	 * @return TalkpageImportOperation
	 */
	public function import( IImportSource $source, Title $targetPage, ImportSourceStore $sourceStore ) {
		$operation = new TalkpageImportOperation( $source );
		$operation->import( new PageImportState(
			// @fixme this is more work than necessary for just the workflow
			$this->workflowLoaderFactory
				->createWorkflowLoader( $targetPage )
				->getWorkflow(),
			$this->storage,
			$sourceStore
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
			array( $time, ++$counter )
		);

		return wfBaseConvert( $binaryUUID, 2, $base );
	}

	/**
	 * Rotate the nodeId to a random one. The stable node is best for
	 * generating "now" uid's on a cluster of servers, but repeated
	 * creation of historical uid's with one or a smaller number of
	 * machines requires use of a random node id.
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
	protected $postIdProperties;

	public function __construct(
		Workflow $boardWorkflow,
		ManagerGroup $storage,
		ImportSourceStore $sourceStore
	) {
		$this->storage = $storage;
		$this->boardWorkflow = $boardWorkflow;
		$this->sourceStore = $sourceStore;

		// Get our workflow UUID property
		$this->workflowIdProperty = new ReflectionProperty( 'Flow\\Model\\Workflow', 'id' );
		$this->workflowIdProperty->setAccessible( true );

		// Get our revision UUID properties
		$this->postIdProperty = new ReflectionProperty( 'Flow\\Model\\PostRevision', 'postId' );
		$this->postIdProperty->setAccessible( true );
		$this->revIdProperty = new ReflectionProperty( 'Flow\\Model\\AbstractRevision', 'revId' );
		$this->revIdProperty->setAccessible( true );
	}

	/**
	 * @param object $object
	 * @param array $metadata
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
	 * @param  string $type Class name to retrieve
	 * @param  UUID   $id   ID of the object to retrieve
	 * @return Object|false
	 */
	public function get( $type, UUID $id ) {
		return $this->storage->get( $type, $id );
	}

	/**
	 * Gets the top revision of an item by ID
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
	 * @param string $timestamp
	 */
	public function setWorkflowTimestamp( Workflow $workflow, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$this->workflowIdProperty->setValue( $workflow, $uid );
	}

	/**
	 * @var AbstractRevision $summary
	 * @var string $timestamp
	 */
	public function setRevisionTimestamp( AbstractRevision $revision, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$setRevId = true;

		if ( $revision instanceof PostRevision ) {
			if ( $revision->isFirstRevision() && $revision->isTopicTitle() ) {
				$setRevId = false;
			} elseif ( $revision->isFirstRevision() && ! $revision->isTopicTitle() ) {
				$this->postIdProperty->setValue( $revision, $uid );
			}
		}

		if ( $setRevId ) {
			$this->revIdProperty->setValue( $revision, $uid );
		}
	}

	/**
	 * Records an association between a created object and its source.
	 * @param  UUID   $objectId  UUID representing the object that was created.
	 * @param  string $objectKey Output from getObjectKey
	 */
	public function recordAssociation( UUID $objectId, $objectKey) {
		$this->sourceStore->setAssociation( $objectId, $objectKey );
	}

	/**
	 * Gets the imported ID for a given object, if any.
	 * @param  IImportObject $object
	 * @return UUID|false
	 */
	public function getImportedId( $object ) {
		return $this->sourceStore->getImportedId( $object->getObjectKey() );
	}

	/**
	 * Commits the association map.
	 */
	public function saveAssociations() {
		$this->sourceStore->save();
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
	 * @param  UUID   $uuid UUID of the modification revision.
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
	 */
	public function import( PageImportState $state ) {
		// echo __METHOD__, ": Importing talkpage at ",
		//	$state->boardWorkflow->getArticleTitle()->getPrefixedText(), "\n";

		if ( $state->boardWorkflow->isNew() ) {
			$state->put( $state->boardWorkflow, array() );
		}

		foreach( $this->importSource->getTopics() as $topic ) {
			$this->importTopic( $state, $topic );
		}
	}

	/**
	 * @param PageImportState $pageState
	 * @param IImportTopic $importTopic
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
		// $database->commit();
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
			return $topicState;
		} else {
			return $this->createTopicState( $state, $importTopic );
		}
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic $importTopic
	 * @return TopicImportState
	 */
	protected function createTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$topicWorkflow = Workflow::create(
			'topic',
			$state->boardWorkflow->getArticleTitle()
		);
		$state->setWorkflowTimestamp(
			$topicWorkflow,
			$importTopic->getCreatedTimestamp()
		);

		$topicListEntry = TopicListEntry::create(
			$state->boardWorkflow,
			$topicWorkflow
		);

		$titleRevisions = $this->importObjectWithHistory(
			$importTopic,
			function( IObjectRevision $rev ) use ( $topicWorkflow ) {
				return PostRevision::create(
					$topicWorkflow,
					$rev->getAuthor(),
					$rev->getText()
				);
			},
			'edit-title',
			$state
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

		$state->recordAssociation( $topicWorkflow->getId(), $importTopic->getObjectKey() );

		return $topicState;
	}

	protected function getExistingTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$topicId = $state->getImportedId( $importTopic );
		if ( $topicId ) {
			$topicWorkflow = $state->get( 'Workflow', $topicId );
			$topicTitle = $state->getTopRevision( 'PostRevision', $topicId );
			if ( $topicWorkflow && $topicTitle ) {
				return new TopicImportState( $state, $topicWorkflow, $topicTitle );
			}
		}

		return false;
	}

	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		$existingId = $state->parent->getImportedId( $importSummary );
		if ( $existingId ) {
			$summary = $state->parent->getTopRevision( 'PostSummary', $existingId );
			if ( $summary ) {
				$state->recordModificationTime( $summary->getRevisionId() );
				return;
			}
		}

		$revisions = $this->importObjectWithHistory(
			$importSummary,
			function( IObjectRevision $rev ) use ( $state ) {
				return PostSummary::create(
					$state->topicWorkflow->getArticleTitle(),
					$state->topicTitle,
					$rev->getAuthor(),
					$rev->getText(),
					'create-topic-summary'
				);
			},
			'edit-topic-summary',
			$state->parent
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
		);
		$state->parent->put( $revisions, $metadata );
		$state->parent->recordAssociation(
			reset( $revisions )->getCollectionId(), // Summary ID
			$importSummary->getObjectKey() // 
		);

		$state->recordModificationTime( end( $revisions )->getRevisionId() );
	}

	/**
	 * Imports an object with all its revisions
	 * @param  IRevisionableObject
	 * Object to import.
	 * @param  callable   $importFirstRevision
	 * Function which, given the appropriate import revision, creates the Flow revision.
	 * @param  string   $editChangeType
	 * The Flow change type (from FlowActions.php) for each new operation.
	 * @param  PageImportOperation   $state
	 * State of the import operation.
	 * @return Array<AbstractRevision> Objects to insert into the database.
	 */
	public function importObjectWithHistory(
		IRevisionableObject $object,
		$importFirstRevision,
		$editChangeType,
		PageImportState $state
	) {
		$insertObjects = array();
		$revisions = $object->getRevisions();
		$revisions->rewind();

		if ( ! $revisions->valid() ) {
			throw new ImportException( "Attempted to import empty history" );
		}

		$importRevision = $revisions->current();
		$insertObjects[] = $lastRevision = $importFirstRevision( $importRevision );
		$lastTimestamp = $importRevision->getTimestamp();

		$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
		$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision->getObjectKey() );
		$state->recordAssociation( $lastRevision->getCollectionId(), $importRevision->getObjectKey() );

		$revisions->next();
		while( $revisions->valid() ) {
			$importRevision = $revisions->current();
			$insertObjects[] = $lastRevision =
				$lastRevision->newNextRevision(
					$importRevision->getAuthor(),
					$importRevision->getText(),
					$editChangeType,
					$state->boardWorkflow->getArticleTitle()
				);

			if ( $importRevision->getTimestamp() < $lastTimestamp ) {
				throw new ImportException( "Revision listing is not sorted from oldest to newest" );
			}

			$lastTimestamp = $importRevision->getTimestamp();
			$state->setRevisionTimestamp( $lastRevision, $lastTimestamp );
			$state->recordAssociation( $lastRevision->getRevisionId(), $importRevision->getObjectKey() );
			$revisions->next();
		}

		return $insertObjects;
	}

	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo
	) {
		$postId = $state->parent->getImportedId( $post );
		$topRevision = false;
		if ( $postId ) {
			$topRevision = $state->parent->getTopRevision( 'PostRevision', $postId );
		}

		if ( ! $topRevision ) {
			$replyRevisions = $this->importObjectWithHistory(
				$post,
				function( IObjectRevision $rev ) use ( $replyTo, $state ) {
					return $replyTo->reply(
						$state->topicWorkflow,
						$rev->getAuthor(),
						$rev->getText()
					);
				},
				'edit-post',
				$state->parent
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
				$post->getObjectKey()
			);
		}

		$state->recordModificationTime( $topRevision->getRevisionId() );

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $topRevision );
		}
	}
}
