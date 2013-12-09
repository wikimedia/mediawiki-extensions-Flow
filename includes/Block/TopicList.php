<?php

namespace Flow\Block;

use Flow\Container;
use Flow\DbFactory;
use Flow\Data\ManagerGroup;
use Flow\Data\ObjectManager;
use Flow\Data\Pager;
use Flow\Data\RootPostLoader;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use User;

class TopicListBlock extends AbstractBlock {

	protected $treeRepo;
	protected $supportedActions = array( 'new-topic' );
	protected $suppressedActions = array( 'board-history' );

	public function __construct(
		Workflow $workflow,
		ManagerGroup $storage,
		NotificationController $notificationController,
		RootPostLoader $rootLoader
	) {
		parent::__construct( $workflow, $storage, $notificationController );
		$this->rootLoader = $rootLoader;
	}

	public function init( $action, $user ) {
		parent::init( $action, $user );
		$this->permissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );
	}

	protected function validate() {
		// for now, new topic is considered a new post; perhaps some day topic creation should get it's own permissions?
		if ( !$this->permissions->isAllowed( null, 'new-post' ) ) {
			$this->addError( 'permissions', wfMessage( 'flow-error-not-allowed' ) );
		} elseif ( !isset( $this->submitted['topic'] ) || !is_string( $this->submitted['topic'] ) ) {
			$this->addError( 'topic', wfMessage( 'flow-error-missing-title' ) );
		} else {
			$this->submitted['topic'] = trim( $this->submitted['topic'] );
			if ( strlen( $this->submitted['topic'] === 0 ) ) {
				$this->addError( 'topic', wfMessage( 'flow-error-missing-title' ) );
			} elseif ( strlen( $this->submitted['topic'] ) > PostRevision::MAX_TOPIC_LENGTH ) {
				$this->addError( 'topic', wfMessage( 'flow-error-title-too-long', PostRevision::MAX_TOPIC_LENGTH ) );
			}
		}
	}

	/**
	 * Create a new topic attached to the current topic list and write it
	 * out to storage. Additionally generates notifications.
	 */
	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new \MWException( 'Unknown commit action' );
		}

		$storage = $this->storage;
		$defStorage = $this->storage->getStorage( 'Definition' );
		$sourceDef = $defStorage->get( $this->workflow->getDefinitionId() );

		if ( ! $sourceDef ) {
			throw new \MWException( "Unable to retrieve definition for this workflow" );
		}

		$topicDef = $defStorage->get( $sourceDef->getOption( 'topic_definition_id' ) );
		if ( !$topicDef ) {
			throw new \MWException( 'Invalid definition owns this TopicList, needs a valid topic_definition_id option assigned' );
		}

		$title = $this->workflow->getArticleTitle();
		$topicWorkflow = Workflow::create( $topicDef, $this->user, $title );

		if ( !$title->exists() ) {
			// if $wgFlowContentFormat is set to html the PostRevision::create
			// call will convert the wikitext input into html via parsoid, and
			// parsoid requires the page exist.
			Container::get( 'occupation_controller' )->ensureFlowRevision( new \Article( $title, 0 ) );	
		}
		$topicPost = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );
		$firstPost = null;
		if ( !empty( $this->submitted['content'] ) ) {
			$firstPost = $topicPost->reply( $this->user, $this->submitted['content'] );
			$topicPost->setChildren( array( $firstPost ) );
		}
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );

		$storage->put( $topicWorkflow );
		$storage->put( $topicListEntry );
		$storage->put( $topicPost );
		if ( $firstPost !== null ) {
			$storage->put( $firstPost );
		}

		$this->notificationController->notifyNewTopic( array(
			'board-workflow' => $this->workflow,
			'topic-workflow' => $topicWorkflow,
			'title-post' => $topicPost,
			'first-post' => $firstPost,
			'user' => $this->user,
		) );

		$user = $this->user;
		$notificationController = $this->notificationController;
		$output = array(
			'created-topic-id' => $topicWorkflow->getId(),
			'created-post-id' => $firstPost ? $firstPost->getRevisionId() : null,
			'render-function' => function( $templating )
					use ( $topicWorkflow, $firstPost, $topicPost, $storage, $user, $notificationController )
			{
				$block = new TopicBlock( $topicWorkflow, $storage, $notificationController, $topicPost );
				return $templating->renderTopic( $topicPost, $block, true );
			},
		);

		return $output;
	}

	public function render( Templating $templating, array $options ) {
		// Don't render the topcilist block for some actions, eg: board-history
		if ( !in_array( $this->action, $this->suppressedActions, true ) ) {
			$templating->getOutput()->addModuleStyles( array( 'ext.flow.discussion', 'ext.flow.moderation' ) );
			$templating->getOutput()->addModules( array( 'ext.flow.discussion' ) );
			if ( $this->workflow->isNew() ) {
				$templating->render( "flow:topiclist.html.php", array(
					'block' => $this,
					'topics' => array(),
					'user' => $this->user,
					'page' => false,
					'permissions' => $this->permissions,
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
					'permissions' => $this->permissions,
				) );
			}
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

	/**
	 * @todo
	 *
	 * when this returns deleted topics they dont get displayed to most
	 * users, which means they will have less topics loaded than they expected.
	 * Not as noticible with inf scroll, but exploitable to force blank page loads.
	 *
	 * One possible solution would be to filter the query at the storage level.
	 *
	 */
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

		if ( ! count( $found ) ) {
			return array();
		}

		$topics = array();
		// @var $entry TopicListEntry
		foreach( $found as $entry ) {
			$topicIds[] = $entry->getId();
		}
		$roots = $this->rootLoader->getMulti( $topicIds );
		foreach ( $topicIds as $idx => $topicId ) {
			if ( !$this->permissions->isAllowed( $roots[$topicId->getHex()], 'view' ) ) {
				unset( $roots[$topicId->getHex()] );
				unset( $topicIds[$idx] );
			}
		}
		foreach ( $roots as $idx => $topicTitle ) {
			if ( !$this->permissions->isAllowed( $topicTitle, 'view' ) ) {
				unset( $roots[$idx] );
			}
		}
		foreach ( $this->storage->getMulti( 'Workflow', $topicIds ) as $workflow ) {
			$hexId = $workflow->getId()->getHex();
			$topics[$hexId] = new TopicBlock( $workflow, $this->storage, $this->notificationController, $roots[$hexId] );
			$topics[$hexId]->init( $this->action, $this->user );
		}

		return $topics;
	}
}

