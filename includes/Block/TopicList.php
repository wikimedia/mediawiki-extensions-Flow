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
			$this->errors['topic'] = wfMessage( 'flow-error-missing-title' );
		}
		if ( !isset( $this->submitted['content'] ) ) {
			$this->errors['content'] = wfMessage( 'flow-error-missing-content' );
		}
	}

	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new \MWException( 'Unknown commit action' );
		}

		$storage = $this->storage;
		$defStorage = $this->storage->getStorage( 'Definition' );
		$sourceDef = $defStorage->get( $this->workflow->getDefinitionId() );
		$topicDef = $defStorage->get( $sourceDef->getOption( 'topic_definition_id' ) );
		if ( !$topicDef ) {
			throw new \MWException( 'Invalid definition owns this TopicList, needs a valid topic_definition_id option assigned' );
		}

		$topicWorkflow = Workflow::create( $topicDef, $this->user, $this->workflow->getArticleTitle() );
		// Should we really have a top level post for the topic title?  Simplifies allowing
		// a revisioned title.
		$topicPost = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );
		$firstPost = $topicPost->reply( $this->user, $this->submitted['content'] );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		$topicPost->setChildren( array( $firstPost ) );
		$firstPost->setChildren( array() );

		$storage->put( $topicWorkflow );
		$storage->put( $topicPost );
		$storage->put( $firstPost );
		$storage->put( $topicListEntry );

		$user = $this->user;

		$output = array(
			'created-topic-id' => $topicWorkflow->getId(),
			'created-post-id' => $firstPost->getRevisionId(),
			'render-function' => function($templating) use ($topicWorkflow, $firstPost, $topicPost, $storage, $user) {
				$block = new TopicBlock( $topicWorkflow, $storage, $topicPost );
				return $templating->renderTopic( $block, $topicWorkflow, $topicPost, $user );
			},
		);

		return $output;
	}

	public function render( Templating $templating, array $options ) {
		$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );
		$templating->render( "flow:topiclist.html.php", array(
			'topicList' => $this,
			'topics' => $this->getTopics(),
			'user' => $this->user,
		) );
	}

	public function renderAPI( array $options ) {
		$output = array( '_element' => 'topic' );
		$topics = $this->getTopics();

		foreach( $topics as $topic ) {
			$output[] = $topic->renderAPI( $options );
		}

		return $output;
	}

	protected function getTopics() {
		// New workflows cant have content yet
		if ( $this->workflow->isNew() ) {
			return array();
		} else {
			return $this->loadAllRelatedTopics();
		}
	}

	public function getName() {
		return 'topic_list';
	}

	protected function loadAllRelatedTopics() {
		$found = $this->storage->find( 'TopicListEntry', array(
			'topic_list_id' => $this->workflow->getId(),
		) );
		if ( !$found ) {
			return array();
		}

		$topics = array();
		foreach( $found as $entry ) {
			$topicIds[] = $entry->getId();
		}
		$roots = $this->rootLoader->getMulti( $topicIds );
		foreach ( $this->storage->getMulti( 'Workflow', $topicIds ) as $workflow ) {
			$hexId = $workflow->getId()->getHex();
			$topics[$hexId] = new TopicBlock( $workflow, $this->storage, $roots[$hexId] );
			$topics[$hexId]->init( $this->action, $this->user );
		}

		return $topics;
	}
}

