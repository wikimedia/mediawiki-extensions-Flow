<?php

namespace Flow\Import;

use Flow\Data\ManagerGroup;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoader;
use Flow\WorkflowLoaderFactory;
use ReflectionClass;
use ReflectionMethod;
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
	public function import( IImportSource $source, Title $targetPage ) {
		$operation = new TalkpageImportOperation( $source );
		$operation->import( new PageImportState(
			$this->workflowLoaderFactory->createWorkflowLoader( $targetPage ),
			$this->storage
		) );
	}
}

class PageImportState {
	/**
	 * @var Workflow
	 */
	public $boardWorkflow;

	/**
	 * @var UIDGenerator
	 */
	protected $uidSingleton;

	/**
	 * @var ReflectionMethod
	 */
	protected $uidMethod;

	/**
	 * @var WorkflowLoader
	 */
	protected $workflowLoader;

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
		WorkflowLoader $workflowLoader,
		ManagerGroup $storage
	) {
		$this->workflowLoader = $workflowLoader;
		$this->storage = $storage;
		$this->boardWorkflow = $workflowLoader->getWorkflow();

		// Get our UIDGenerator singleton
		$uidGeneratorClass = new ReflectionClass( 'UIDGenerator' );
		$singletonMethod = $uidGeneratorClass->getMethod( 'singleton' );
		$singletonMethod->setAccessible( true );
		$this->uidSingleton = $singletonMethod->invoke( null );

		// Get our getTimestampedID88 method
		$this->uidMethod = $uidGeneratorClass->getMethod( 'getTimestampedID88' );
		$this->uidMethod->setAccessible( true );

		// Get our workflow UUID property
		$this->workflowIdProperty = new ReflectionProperty( 'Flow\\Model\\Workflow', 'id' );
		$this->workflowIdProperty->setAccessible( true );

		// Get our revision ID properties
		foreach( array( 'postId', 'revId' ) as $propName ) {
			$property = new ReflectionProperty( 'Flow\\Model\\PostRevision', $propName );
			$property->setAccessible( true );
			$this->postIdProperties[$propName] = $property;
		}
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

		$binaryUUID = $this->uidMethod->invoke(
			$this->uidSingleton,
			array( $time, ++$counter )
		);
		$uuid = wfBaseConvert( $binaryUUID, 2, 10 );
		return UUID::create( $uuid );
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

		foreach( $this->postIdProperties as $prop ) {
			$prop->setValue( $post, $uid );
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

	public function __construct(
		PageImportState $parent,
		Workflow $topicWorkflow,
		PostRevision $topicTitle
	) {
		$this->parent = $parent;
		$this->topicWorkflow = $topicWorkflow;
		$this->topicTitle = $topicTitle;
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
		echo __METHOD__, ": Importing talkpage at ",
			 $state->boardWorkflow->getArticleTitle()->getPrefixedText(), "\n";

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
		echo "\n", __METHOD__, ": Importing topic: ", $importTopic->getText(), "\n";
		$topicState = $this->createTopicState( $pageState, $importTopic );

		$summary = $importTopic->getTopicSummary();
		if ( $summary ) {
			$this->importSummary( $topicState, $summary );
		}

		foreach ( $importTopic->getReplies() as $post ) {
			$this->importPost( $topicState, $post, $topicState->topicTitle );
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

		$topicMetadata = array(
			'workflow' => $topicWorkflow,
			'board-workflow' => $state->boardWorkflow,
			'topic-title' => $topicTitle,
		);

		$state->put( $topicTitle, $topicMetadata );
		$state->put( $topicListEntry, $topicMetadata );
		$state->put( $topicWorkflow, $topicMetadata );

		return new TopicImportState( $state, $topicWorkflow, $topicTitle );
	}

	public function importSummary( TopicImportState $state, IImportSummary $importSummary ) {
		echo __METHOD__, ": Importing summary\n";

		$summary = PostSummary::create(
			$state->topicTitle,
			$importSummary->getAuthor(),
			$importSummary->getText(),
			'create-topic-summary'
		);

		$metadata = array(
			'workflow' => $state->topicWorkflow,
		);
		$state->parent->put( $summary, $metadata );
	}

	public function importPost(
		TopicImportState $state,
		IImportPost $post,
		PostRevision $replyTo
	) {
		echo __METHOD__, ": Importing post from ", $post->getAuthor()->getName(), "\n";
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

		foreach ( $post->getReplies() as $subReply ) {
			$this->importPost( $state, $subReply, $replyPost );
		}
	}
}

class SummaryImportOperation {
	/** @var TopicImportState */
	protected $state;
	/** @var IImportSummary **/
	protected $summary;

	public function __construct( TopicImportState $state, IImportSummary $summary ) {
		$this->state = $state;
		$this->summary = $summary;
	}

}

