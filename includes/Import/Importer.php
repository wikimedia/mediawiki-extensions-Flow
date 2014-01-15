<?php

namespace Flow\Import;

class Importer {
	function __construct( Storage $storage, WorkflowLoaderFactory $workflowLoaderFactory ) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
	}

	/**
	 * Imports topics from a data source to a given page.
	 * @param  ImportSource $source
	 * @param  Title        $targetPage
	 */
	function import( ImportSource $source, Title $targetPage ) {
		foreach( $source->getTopics() as $topic ) {
			$this->importTopic( $source, $topic, $targetPage );
		}
	}

	function importTopic( ImportSource $source, ImportTopic $topic, Title $targetPage ) {
		$workflowLoader = $this->workflowLoaderFactory->createWorkflowLoader( $targetPage );

		$workflow = Workflow::create( 'topic', $topic->getCreator(), $targetPage );
		$this->setTimestampedId( $workflow, $topic->getCreatedTimestamp() );
		$this->storage->put( $workflow );

		$topicListEntry = TopicListEntry::create( $workflow, $workflowLoader->getWorkflow() );
		$this->storage->put( $topicListEntry );

		$topPost = PostRevision::create( $workflow, $topic->getSubject() );
		$this->setTimestampedId( $topPost, $topic->getModifiedTimestamp() );
		$this->storage->put( $topPost );

		$summary = $source->getTopicSummary( $topic );

		if ( $summary ) {
			$this->importSummary( $workflow, $summary );
		}

		foreach( $source->getTopicPosts() as $post ) {
			$this->importReply( $source, $post, $topPost );
		}
	}
}