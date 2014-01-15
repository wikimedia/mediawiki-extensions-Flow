<?php

use Flow\Container;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;

use \Thread;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertLqt extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts LiquidThreads data to Flow data";
	}

	public function execute() {
		// Read LQT threads one at a time
		$dbr = wfGetDB( DB_MASTER );
		$nsOffset = 0;
		$titleOffset = '';
		$idOffset = 0;

		if ( ! class_exists( 'Thread' ) ) {
			die( "This script requires that the LiquidThreads extension is loaded\n" );
		}

		while ( true ) {
			// Loop through top level threads on each page
			$res = $dbr->select(
				'thread',
				'*',
				array(
					'thread_article_namespace >= ' . $dbr->addQuotes( $nsOffset ),
					'thread_article_title >= ' . $dbr->addQuotes( $titleOffset ),
					'thread_id > ' . $dbr->addQuotes( $idOffset ),
					'thread_ancestor' => 0,
				),
				__METHOD__,
				array(
					'LIMIT' => 500,
					'ORDER BY' => 'thread_article_namespace ASC, thread_article_title ASC, thread_id ASC',
				)
			);

			if ( ! $res || $res->numRows() === 0 ) {
				break;
			}

			$rows = array();
			foreach( $res as $row ) { $rows[] = $row; };
			$topLevelThreads = Thread::bulkLoad( $rows );

			foreach( $topLevelThreads as $thread ) {
				$this->processTopmostThread( $thread );
				$idOffset = $thread->id();
			}
		}
	}

	protected function processTopmostThread( $thread ) {
		static $workflowLoaderFactory;
		static $occupationController;
		static $storage;

		if ( ! $workflowLoaderFactory ) {
			$workflowLoaderFactory = Flow\Container::get( 'factory.loader.workflow' );
			$occupationController = Flow\Container::get( 'occupation_controller' );
			$storage = Flow\Container::get( 'storage' );
		}

		$workflowLoader = $workflowLoaderFactory->createWorkflowLoader( $thread->getTitle() );
		$occupationController->ensureFlowRevision( $thread->article() );

		$talkPageWorkflow = $workflowLoader->getWorkflow();
		$storage->put( $talkPageWorkflow );

		$topicDefinition = $storage->get( 'Definition', $workflowLoader->getDefinition()->getOption( 'topic_definition_id' ) );
		$topicWorkflow = Workflow::create( $topicDefinition, $thread->author(), $thread->getTitle() );
		$topicWorkflow->setID( UUID::getComparisonUUID( $thread->created() ) );

		$storage->put( $topicWorkflow );

		$topicListEntry = TopicListEntry::create( $talkPageWorkflow, $topicWorkflow );
		$storage->put( $topicListEntry );

		$topPost = PostRevision::create( $topicWorkflow, $thread->subject() );
		$topPost->setRevisionId( UUID::getComparisonUUID( $thread->modified() ) );

		$storage->put( $topPost );

		$contentPost = $topPost->reply( $topicWorkflow, $thread->author(), $thread->root()->getContent() );

		$contentPost->setPostId( UUID::getComparisonUUID( $thread->created() ) );
		$contentPost->setRevisionId( UUID::getComparisonUUID( $thread->modified() ) );

		$storage->put( $contentPost );

		$posts = $this->processThreadReplies( $topicWorkflow, $thread, $contentPost );

		foreach( $posts as $post ) {
			$storage->put( $post );
		}
	}

	protected function processThreadReplies( Workflow $workflow, Thread $thread, PostRevision $post ) {
		$flowReplies = array();
		foreach( $thread->replies() as $lqtReply ) {
			$flowReply = $post->reply(
				$workflow,
				$lqtReply->author(),
				$lqtReply->root()->getContent()
			);

			$flowReply->setPostId( UUID::getComparisonUUID( $thread->created() ) );
			$flowReply->setRevisionId( UUID::getComparisonUUID( $thread->modified() ) );

			$flowReplies = array_merge( $flowReplies,
				$this->processThreadReplies( $workflow, $lqtReply, $flowReply )
			);

			$flowReplies[] = $flowReply;
		}

		return $flowReplies;
	}

	/**
	 * Fetches the Definition for the topics to create.
	 * @return Flow\Model\Definition
	 */
	protected function getTopicDefinition() {
		$results = Flow\Container::get( 'storage' )
				->find( 'Definition', array(
					'definition_name' => 'topic',
				)
			);

		return reset( $results );
	}
}

$maintClass = "ConvertLqt";
require_once( RUN_MAINTENANCE_IF_MAIN );