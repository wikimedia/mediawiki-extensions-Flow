<?php

namespace Flow\Import;

use Flow\Data\ManagerGroup;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use MWCryptRand;
use ReflectionProperty;
use Title;
use UIDGenerator;

/**
 * The import system uses a hierarchy of ImportOperations.
 * Each ImportOperation can have a parent operation, from which
 * much of its data is drawn. The hierarchy goes something like:
 * Wiki -> Talkpage -> Topic -> (Post, Summary) -> Post
 *
 * The Importer class is an entry point that creates the correct ImportOperation
 * classes and executes them. It's there to make the dependency injection less
 * inconvenient for callers.
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
class OldUIDGenerator extends UIDGenerator {
	public static function oldTimestampedUID88( $timestamp, $base = 10 ) {
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
		$this->storage->put( $object, $metadata );
	}

	/**
	 * Creates a UUID object representing a given timestamp.
	 *
	 * @param string $timestamp The timestamp to represent, in a wfTimestamp compatible format.
	 * @return UUID
	 */
	public function getTimestampId( $timestamp ) {
		return UUID::create( OldUIDGenerator::oldTimestampedUID88( $timestamp ) );
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
	 * @var PostRevision $post
	 * @var string $timestamp
	 */
	public function setPostTimestamp( PostRevision $post, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		$this->revIdProperty->setValue( $post, $uid );
		// Topic titles source their postId from the workflow,
		// non-first revisions source their postId from the parent
		if ( $post->isFirstRevision() && !$post->isTopicTitle() ) {
			$this->postIdProperty->setValue( $post, $uid );
		}
	}

	/**
	 * @var Summary $summary
	 * @var string $timestamp
	 */
	public function setSummaryTimestamp( PostSummary $summary, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );
		$this->revIdProperty->setValue( $summary, $uid );
	}

	/**
	 * Records an association between a created object and its source.
	 * @param  UUID   $objectId  UUID representing the object that was created.
	 * @param  string $objectKey Output from getObjectKey
	 */
	public function recordAssociation( UUID $objectId, $objectKey) {
		$this->sourceStore->setAssociation( $objectId, $objectKey );
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
		//echo "\n", __METHOD__, ": Importing topic: ", $importTopic->getText(), "\n";
		$topicState = $this->createTopicState( $pageState, $importTopic );

		$summary = $importTopic->getTopicSummary();
		if ( $summary ) {
			$this->importSummary( $topicState, $summary );
		}

		foreach ( $importTopic->getReplies() as $post ) {
			$this->importPost( $topicState, $post, $topicState->topicTitle );
		}

		$topicState->commitLastModified();
	}

	/**
	 * @param PageImportState $state
	 * @param IImportTopic $importTopic
	 * @return TopicImportState
	 */
	protected function createTopicState( PageImportState $state, IImportTopic $importTopic ) {
		$topicWorkflow = Workflow::create(
			'topic',
			$importTopic->getAuthor(),
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

		$topicTitle = PostRevision::create(
			$topicWorkflow,
			$importTopic->getText()
		);
		$state->setPostTimestamp(
			$topicTitle,
			$importTopic->getCreatedTimestamp()
		);

		$topicState = new TopicImportState( $state, $topicWorkflow, $topicTitle );
		$topicMetadata = $topicState->getMetadata();

		// TLE must be first, otherwise you get an error importing TopicTitle
		// Flow/includes/Data/Index/BoardHistoryIndex.php:
		// No topic list contains topic XXX, called for revision YYY
		$state->put( $topicListEntry, $topicMetadata );
		// TopicTitle must be second, because inserting topicWorkflow requires
		// the topic title to already be in place
		$state->put( $topicTitle, $topicMetadata );
		$state->put( $topicWorkflow, $topicMetadata );

		$state->recordAssociation( $topicWorkflow->getId(), $importTopic->getObjectKey() );

		return $topicState;
	}

	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		//echo __METHOD__, ": Importing summary\n";

		$summary = PostSummary::create(
			$state->topicWorkflow->getArticleTitle(),
			$state->topicTitle,
			$importSummary->getAuthor(),
			$importSummary->getText(),
			'create-topic-summary'
		);

		$state->parent->setSummaryTimestamp(
			$summary,
			$importSummary->getCreatedTimestamp()
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
		);
		$state->parent->put( $summary, $metadata );

		$state->recordModificationTime( $summary->getRevisionId() );
	}

	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo
	) {
		//echo __METHOD__, ": Importing post from ", $post->getAuthor()->getName(), "\n";
		$replyPost = $replyTo->reply(
			$state->topicWorkflow,
			$post->getAuthor(),
			$post->getText()
		);
		$state->parent->setPostTimestamp(
			$replyPost,
			$post->getCreatedTimestamp()
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
			'board-workflow' => $state->parent->boardWorkflow,
			'topic-title' => $state->topicTitle,
			'reply-to' => $replyTo,
		);

		$state->parent->put( $replyPost, $metadata );
		$state->parent->recordAssociation(
			$replyPost->getPostId(),
			$post->getObjectKey()
		);

		$state->recordModificationTime( $replyPost->getRevisionId() );

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $replyPost );
		}
	}
}
