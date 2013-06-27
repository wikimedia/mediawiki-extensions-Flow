<?php

namespace Flow\Block;

use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Data\ObjectManager;
use Flow\Data\RootPostLoader;
use Flow\DbFactory;
use Flow\Templating;
use User;

class TopicListBlock extends AbstractBlock {

	protected $treeRepo;
	protected $supportedActions = array( 'new-topic' );

	public function __construct( Workflow $workflow, ManagerGroup $storage, RootPostLoader $rootLoader ) {
		$this->workflow = $workflow;
		$this->storage = $storage;
		$this->rootLoader = $rootLoader;
	}

	protected function validate() {
		if ( !isset( $this->submitted['topic'] ) ) {
			$this->errors['topic'] = wfMessage( 'flow-missing-topic-title' );
		}
		if ( !isset( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-missing-post-content' );
		}
	}

	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new \MWException( 'Unknown commit action' );
		}

		$defStorage = $this->storage->getStorage( 'Definition' );
		$sourceDef = $defStorage->get( $this->workflow->getDefinitionId() );
		$topicDef = $defStorage->get( $sourceDef->getOption( 'topic_definition_id' ) );
		if ( !$topicDef ) {
			throw new \MWException( 'Invalid definition owns this TopicList, needs a valid topic_definition_id option assigned' );
		}

		$topicWorkflow = Workflow::create( $topicDef, $this->user, $this->workflow->getArticleTitle() );
		// Should we really have a top level post for the topic title?
		$topicPost = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );
		$firstPost = $topicPost->reply( $this->user, $this->submitted['content'] );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );

		$this->storage->put( $topicWorkflow );
		$this->storage->put( $topicPost );
		$this->storage->put( $firstPost );
		$this->storage->put( $topicListEntry );
	}

	public function render( Templating $templating, array $options ) {
		$topics = array();

		// New workflows cant have content yet
		if ( !$this->workflow->isNew() ) {
			$topicList = $this->storage->find( 'TopicListEntry', array(
				'topic_list_id' => $this->workflow->getId(),
			) );
			if ( $topicList ) {
				foreach( $topicList as $entry ) {
					$topicIds[] = $entry->getId();
				}
				$roots = $this->rootLoader->getMulti( $topicIds );
				foreach ( $this->storage->getMulti( 'Workflow', $topicIds ) as $workflow ) {
					$id = $workflow->getId();
					$topics[$id] = new TopicBlock( $workflow, $this->storage, $roots[$id] );
				}
			}
		}

		$templating->render( "flow:topiclist.html.php", array(
			'topicList' => $this,
			'topics' => $topics,
		) );
	}

	public function getName() {
		return 'topic_list';
	}
}

