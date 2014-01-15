<?php

namespace Flow\Import;

use Flow\WorkflowLoader;
use Flow\Data\ManagerGroup;
use Flow\WorkflowLoaderFactory;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use ReflectionClass;
use Title;

class Importer {
	function __construct( ManagerGroup $storage, WorkflowLoaderFactory $workflowLoaderFactory ) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
	}

	/**
	 * Imports topics from a data source to a given page.
	 * @param  ImportSource $source
	 * @param  Title        $targetPage
	 */
	function import( ImportSource $source, Title $targetPage ) {
		$workflowLoader = $this->workflowLoaderFactory->createWorkflowLoader( $targetPage );
		if ( $workflowLoader->getWorkflow()->isNew() ) {
			// I don't know what to do here
			// $workflowLoader->getWorkflow()->insert();
		}

		foreach( $source->getTopics() as $topic ) {
			$this->importTopic( $source, $topic, $workflowLoader );
		}
	}

	function importTopic( ImportSource $source, ImportTopic $topic, WorkflowLoader $workflowLoader ) {
		$boardWorkflow = $workflowLoader->getWorkflow();

		$workflow = Workflow::create( 'topic', $topic->getCreator(), $boardWorkflow->getArticleTitle() );
		$this->setWorkflowTimestamp( $workflow, $topic->getCreatedTimestamp() );

		$topicMetadata = array( 'workflow' => $workflow );

		$topicListEntry = TopicListEntry::create( $workflow, $workflowLoader->getWorkflow() );

		$topPost = PostRevision::create( $workflow, $topic->getSubject() );

		$this->storage->put( $topPost, $topicMetadata );
		$this->storage->put( $topicListEntry, $topicMetadata );
		$this->storage->put( $workflow, $topicMetadata );

		$summary = $source->getTopicSummary( $topic );

		if ( $summary ) {
			$this->importSummary( $workflow, $summary );
		}

		foreach( $source->getTopicPosts( $topic ) as $post ) {
			$this->importReply( $source, $workflow, $post, $topPost );
		}
	}

	function importReply( ImportSource $source, Workflow $workflow, ImportPost $post, PostRevision $replyTo ) {
		$replyPost = $replyTo->reply( $workflow, $post->getAuthor(), $post->getText() );

		$this->setPostTimestamp( $replyPost, $post->getCreatedTimestamp() );
		$this->storage->put( $replyPost, array( 'workflow' => $workflow, 'reply-to' => $replyTo->getPostId() ) );

		foreach( $source->getPostReplies( $post ) as $subreply ) {
			$this->importReply( $source, $workflow, $subreply, $replyPost );
		}
	}

	function getTimestampId( $timestamp ) {
		$timestamp = wfTimestamp( TS_UNIX, $timestamp );

		$uidGeneratorClass = new ReflectionClass( 'UIDGenerator' );
		$singletonMethod = $uidGeneratorClass->getMethod( 'singleton' );
		$singletonMethod->setAccessible( true );
		$uidGenerator = $singletonMethod->invoke( null );

		static $counter = false;
		if ( $counter === false ) {
			$counter = mt_rand( 0, 256 );
		}
		++$counter;

		$time = array( $timestamp, mt_rand( 0, 1000 ) );
		$timestampMethod = $uidGeneratorClass->getMethod( 'getTimestampedID88' );
		$timestampMethod->setAccessible( true );
		$binaryUUID = $timestampMethod->invoke( $uidGenerator, array( $time, $counter ) );
		$uuid = wfBaseConvert( $binaryUUID, 2, 10 );
		return UUID::create( $uuid );
	}

	function setWorkflowTimestamp( Workflow $workflow, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		$workflowClass = new ReflectionClass( 'Flow\\Model\\Workflow' );
		$idProperty = $workflowClass->getProperty( 'id' );
		$idProperty->setAccessible( true );
		$idProperty->setValue( $workflow, $uid );
	}

	function setPostTimestamp( PostRevision $post, $timestamp ) {
		$uid = $this->getTimestampId( $timestamp );

		$postClass = new ReflectionClass( 'Flow\\Model\\PostRevision' );

		foreach( array( 'revId', 'postId' ) as $propName ) {
				$prop = $postClass->getProperty( $propName );
				$prop->setAccessible( true );
				$prop->setValue( $post, $uid );
		}
	}
}