<?php

namespace Flow\Import;

use Flow\WorkflowLoader;
use Flow\Data\ManagerGroup;
use Flow\WorkflowLoaderFactory;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use ReflectionClass;
use ReflectionProperty;
use Title;

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
	 * @param  IImportSource $source
	 * @param  Title        $targetPage
	 */
	public function import( IImportSource $source, Title $targetPage) {
		$workflowLoader = $this->workflowLoaderFactory->createWorkflowLoader( $targetPage );
		$operation = new TalkpageImportOperation( $source, $workflowLoader, $this->storage );
		$operation->import();
	}
}

abstract class ImportOperation {
	protected $parent;
	protected $uidSingleton, $uidMethod, $workflowIdProperty, $postIdProperties;

	public function __construct( ImportOperation $parent = null ) {
		$this->parent = $parent;
		$this->initReflection();
	}

	/**
	 * Initialises reflection objects.
	 * We use Reflection because we don't want to allow ID mutation
	 * in the public interface of our object model. Importing is
	 * something of a special case.
	 */
	protected function initReflection() {
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
		$this->postIdProperties = array();
		foreach( array( 'postId', 'revId' ) as $propName ) {
			$property = new ReflectionProperty( 'Flow\\Model\\PostRevision', $propName );
			$property->setAccessible( true );
			$this->postIdProperties[$propName] = $property;
		}
	}

	/**
	 * Handler that allows us to cascade data down the call chain.
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function __get( $name ) {
		if ( ! isset( $this->$name ) && ! is_null( $this->parent ) ) {
			return $this->parent->$name;
		}
	}

	abstract function import();

	/**
	 * Creates a UUID object representing a given timestamp.
	 * @param  string $timestamp The timestamp to represent, in a wfTimestamp compatible format.
	 * @return UUID
	 */
	protected function getTimestampId( $timestamp ) {
		$timestamp = wfTimestamp( TS_UNIX, $timestamp );

		static $counter = false;
		if ( $counter === false ) {
			$counter = mt_rand( 0, 256 );
		}
		++$counter;

		$time = array( $timestamp, mt_rand( 0, 1000 ) );

		$binaryUUID = $this->uidMethod->invoke(
			$this->uidSingleton,
			array( $time, $counter )
		);
		$uuid = wfBaseConvert( $binaryUUID, 2, 10 );
		return UUID::create( $uuid );
	}
}

class TalkpageImportOperation extends ImportOperation {
	/** @var IImportSource **/
	public $IImportSource;
	/** @var WorkflowLoader **/
	public $workflowLoader;
	/** @var ManagerGroup **/
	public $storage;

	/**
	 * @param IImportSource   $IImportSource
	 * @param WorkflowLoader $workflowLoader
	 */
	public function __construct(
		IImportSource $IImportSource,
		WorkflowLoader $workflowLoader,
		ManagerGroup $storage
	) {
		parent::__construct();
		$this->IImportSource = $IImportSource;
		$this->workflowLoader = $workflowLoader;
		$this->storage = $storage;
	}

	public function import() {
		$workflowLoader = $this->workflowLoader;

		if ( $workflowLoader->getWorkflow()->isNew() ) {
			$this->storage->getStorage( 'Workflow' )
				->put( $workflowLoader->getWorkflow() );
		}

		foreach( $this->IImportSource->getTopics() as $topic ) {
			$topicOperation = new TopicImportOperation( $this, $topic );
			$topicOperation->import();
		}
	}
}

class TopicImportOperation extends ImportOperation {
	/** @var Workflow **/
	public $topicWorkflow;
	/** @var IImportTopic **/
	public $IImportTopic;
	/** @var PostRevision **/
	public $topicTitle;

	public function __construct( TalkpageImportOperation $parent, IImportTopic $IImportTopic ) {
		parent::__construct( $parent );
		$this->IImportTopic = $IImportTopic;
	}

	/**
	 * Imports a topic from a source to a given page.
	 */
	public function import() {
		$source = $this->IImportSource;
		$workflowLoader = $this->workflowLoader;
		$boardWorkflow = $workflowLoader->getWorkflow();
		$IImportTopic = $this->IImportTopic;

		$workflow = Workflow::create(
			'topic',
			$IImportTopic->getCreator(),
			$boardWorkflow->getArticleTitle()
		);
		$this->setWorkflowTimestamp( $workflow, $this->IImportTopic->getCreatedTimestamp() );

		$topicListEntry = TopicListEntry::create( $boardWorkflow, $workflow );

		$topPost = PostRevision::create( $workflow, $IImportTopic->getSubject() );

		$topicMetadata = array(
			'workflow' => $workflow,
			'board-workflow' => $boardWorkflow,
			'topic-title' => $topPost,
		);

		$this->storage->put( $topPost, $topicMetadata );
		$this->storage->put( $topicListEntry, $topicMetadata );
		$this->storage->put( $workflow, $topicMetadata );

		$summary = $source->getTopicSummary( $IImportTopic );

		if ( $summary ) {
			$summaryOperation = new SummaryImportOperation( $this, $summary );
			$summaryOperation->import();
		}

		// Save data for replies
		$this->topicWorkflow = $workflow;
		$this->topicTitle = $topPost;

		foreach( $source->getTopicPosts( $IImportTopic ) as $post ) {
			$operation = new PostImportOperation( $this, $post, $topPost );
			$operation->import();
		}
	}

	protected function setWorkflowTimestamp( Workflow $workflow, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		$this->workflowIdProperty->setValue( $workflow, $uid );
	}
}

class PostImportOperation extends ImportOperation {
	/** @var IImportPost **/
	public $post;
	/** @var PostRevision **/
	public $replyTo;

	public function __construct(
		ImportOperation $parent,
		IImportPost $post,
		PostRevision $replyTo
	) {
		parent::__construct( $parent );
		$this->post = $post;
		$this->replyTo = $replyTo;
	}

	/**
	 * Imports a reply to a given post.
	 */
	public function import() {
		$post = $this->post;
		$replyTo = $this->replyTo;
		$workflow = $this->topicWorkflow;

		$replyPost = $replyTo->reply( $workflow, $post->getAuthor(), $post->getText() );
		$this->setPostTimestamp( $replyPost, $post->getCreatedTimestamp() );

		$metadata = array(
			'workflow' => $workflow,
			'board-workflow' => $this->workflowLoader->getWorkflow(),
			'topic-title' => $this->topicTitle,
			'reply-to' => $replyTo,
		);

		$this->storage->put( $replyPost, $metadata );

		foreach( $this->IImportSource->getPostReplies( $post ) as $subreply ) {
			$operation = new PostImportOperation( $this, $subreply, $replyPost );
			$operation->import();
		}
	}

	protected function setPostTimestamp( PostRevision $post, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		foreach( $this->postIdProperties as $propName => $prop ) {
			$prop->setValue( $post, $uid );
		}
	}
}

class SummaryImportOperation extends ImportOperation {
	/** @var IImportSummary **/
	public $summary;

	public function __construct( TopicImportOperation $parent, IImportSummary $summary ) {
		parent::__construct( $parent );
		$this->summary = $summary;
	}

	public function import() {
		$summary = PostSummary::create(
			$this->topicTitle,
			$this->summary->getUser(),
			$this->summary->getText(),
			'create-topic-summary'
		);
	}
}

