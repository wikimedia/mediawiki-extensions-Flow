<?php

namespace Flow\Block;

use Flow\Data\ManagerGroup;
use Flow\Data\ObjectManager;
use Flow\Data\Pager;
use Flow\Data\RootPostLoader;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\Templating;
use User;

class TopicListBlock extends AbstractBlock {

	protected $treeRepo;
	protected $supportedActions = array( 'new-topic' );

	public function __construct(
		Workflow $workflow,
		ManagerGroup $storage,
		NotificationController $notificationController,
		RootPostLoader $rootLoader
	) {
		parent::__construct( $workflow, $storage, $notificationController );
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

		// build topic
		$topicWorkflow = $this->findWorkflow( 'topic_definition_id' );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		// Should we really have a top level post for the topic title?  Simplifies allowing
		// a revisioned title.
		$topicPost = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );

		// build initial post in this topic
		$topicBlock = new TopicBlock( $topicWorkflow, $this->storage, $this->notificationController, $topicPost );
		$topicBlock->init( $this->action, $this->user ); // @todo: this $action won't actually exist in TopicBlock
		$postWorkflow = $topicBlock->findWorkflow( 'post_definition_id' );
		$firstPost = PostRevision::create( $postWorkflow, $this->submitted['content'] );
		$topicPost->setChildren( array( $firstPost ) );
		$firstPost->setChildren( array() );

		// save to storage
		$this->storage->put( $topicListEntry );
		$this->storage->put( $topicWorkflow );
		$this->storage->put( $topicPost );
		$this->storage->put( $postWorkflow );
		$this->storage->put( $firstPost );

		$this->notificationController->notifyNewTopic( array(
			'board-workflow' => $this->workflow,
			'topic-workflow' => $topicWorkflow,
			'title-post' => $topicPost,
			'first-post' => $firstPost,
			'user' => $this->user,
		) );

		$user = $this->user;
		$action = $this->action;
		$output = array(
			'created-topic-id' => $topicWorkflow->getId(),
			'created-post-id' => $firstPost->getRevisionId(),
			'render-function' => function( $templating ) use ( $topicBlock, $topicPost, $user, $action ) {
				$topicBlock->init( $action, $user );
				return $topicBlock->render( $templating, array(), true );
			},
		);

		return $output;
	}

	public function render( Templating $templating, array $options ) {
		$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );

		if ( $this->workflow->isNew() ) {
			$templating->render( "flow:topiclist.html.php", array(
				'block' => $this,
				'topics' => array(),
				'user' => $this->user,
				'page' => false,
			) );
		} else {
			$findOptions = $this->getFindOptions( $options );
			$page = $this->getPage( $findOptions );
			$topics = $this->getTopics( $page );

			$templating->render( "flow:topiclist.html.php", array(
				'block' => $this,
				'topics' => $topics,
				'user' => $this->user,
				'page' => $page,
			) );
		}
	}

	public function renderAPI( Templating $templating, array $options ) {
		$output = array( '_element' => 'topic' );
		if ( ! $this->workflow->isNew() ) {
			$findOptions = $this->getFindOptions( $options + array( 'api' => true ) );
			$page = $this->getPage( $findOptions );
			$topics = $this->getTopics( $page );

			foreach( $topics as $topic ) {
				$output[] = $topic->renderAPI( $templating, $options );
			}

			$output['paging'] = $page->getPagingLinks();
		}

		return $output;
	}

	public function getName() {
		return 'topic_list';
	}

	protected function getLimit( $options ) {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit;
		$limit = $wgFlowDefaultLimit;
		if ( isset( $options['limit'] ) ) {
			$requestedLimit = intval( $options['limit'] );
			if ( $requestedLimit > 0 && $requestedLimit < $wgFlowMaxLimit ) {
				$limit = $requestedLimit;
			}
		}

		return $limit;
	}

	protected function getFindOptions( $requestOptions ) {
		$findOptions = array();

		// Compute offset/limit
		$limit = $this->getLimit( $requestOptions );

		if ( isset( $requestOptions['offset-id'] ) ) {
			$findOptions['pager-offset'] = UUID::create( $requestOptions['offset-id'] );
		} elseif ( isset( $requestOptions['offset'] ) ) {
			$findOptions['pager-offset'] = intval( $requestOptions['offset'] );
		}

		if ( isset( $requestOptions['offset-dir'] ) ) {
			$findOptions['pager-dir'] = $requestOptions['offset-dir'];
		}

		if ( isset( $requestOptions['api'] ) ) {
			$findOptions['offset-elastic'] = false;
		}

		$findOptions['pager-limit'] = $limit;

		return $findOptions;
	}

	protected function getPage( $findOptions ) {
		$pager = new Pager(
			$this->storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $this->workflow->getId() ),
			$findOptions
		);

		return $pager->getPage();
	}

	protected function getTopics( $page ) {
		$found = $page->getResults();

		if ( !count( $found ) ) {
			return array();
		}

		$topics = array();
		foreach( $found as $entry ) {
			$topicIds[] = $entry->getId();
		}
		$roots = $this->rootLoader->getMulti( $topicIds );
		foreach ( $this->storage->getMulti( 'Workflow', $topicIds ) as $workflow ) {
			$hexId = $workflow->getId()->getHex();
			$topics[$hexId] = new TopicBlock( $workflow, $this->storage, $this->notificationController, $roots[$hexId] );
			$topics[$hexId]->init( $this->action, $this->user );
		}

		return $topics;
	}
}
