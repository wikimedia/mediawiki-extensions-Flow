<?php

namespace Flow\Block;

use ApiResult;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\Pager;
use Flow\Data\PagerPage;
use Flow\Data\RootPostLoader;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\Exception\FailCommitException;

class TopicListBlock extends AbstractBlock {

	/**
	 * @var array
	 */
	protected $supportedPostActions = array( 'new-topic' );

	/**
	 * @var array
	 */
	protected $supportedGetActions = array( 'view' );

	/**
	 * @var Workflow|null
	 */
	protected $topicWorkflow;

	/**
	 * @var TopicListEntry|null
	 */
	protected $topicListEntry;

	/**
	 * @var PostRevision|null
	 */
	protected $topicPost;

	/**
	 * @var PostRevision|null
	 */
	protected $firstPost;

	/**
	 * @var RevisionActionPermissions
	 */
	protected $permissions;

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
			return;
		}
		if ( !isset( $this->submitted['topic'] ) || !is_string( $this->submitted['topic'] ) ) {
			$this->addError( 'topic', wfMessage( 'flow-error-missing-title' ) );
			return;
		}
		$this->submitted['topic'] = trim( $this->submitted['topic'] );
		if ( strlen( $this->submitted['topic'] ) === 0 ) {
			$this->addError( 'topic', wfMessage( 'flow-error-missing-title' ) );
			return;
		}
		if ( mb_strlen( $this->submitted['topic'] ) > PostRevision::MAX_TOPIC_LENGTH ) {
			$this->addError( 'topic', wfMessage( 'flow-error-title-too-long', PostRevision::MAX_TOPIC_LENGTH ) );
			return;
		}

		// creates Workflow, Revision & TopicListEntry objects to be inserted into storage
		list( $this->topicWorkflow, $this->topicListEntry, $this->topicPost, $this->firstPost ) = $this->create();

		if ( !$this->checkSpamFilters( null, $this->topicPost ) ) {
			return;
		}
		if ( $this->firstPost && !$this->checkSpamFilters( null, $this->firstPost ) ) {
			return;
		}
	}

	/**
	 * Creates the objects about to be inserted into storage:
	 * * $this->topicWorkflow
	 * * $this->topicListEntry
	 * * $this->topicPost
	 * * $this->firstPost
	 *
	 * @throws \MWException
	 * @throws \Flow\Exception\FailCommitException
	 * @return array Array of [$topicWorkflow, $topicListEntry, $topicPost, $firstPost]
	 */
	protected function create() {
		$title = $this->workflow->getArticleTitle();
		$topicWorkflow = Workflow::create( 'topic', $this->user, $title );
		$topicListEntry = TopicListEntry::create( $this->workflow, $topicWorkflow );
		$topicPost = PostRevision::create( $topicWorkflow, $this->submitted['topic'] );

		$firstPost = null;
		if ( !empty( $this->submitted['content'] ) ) {
			$firstPost = $topicPost->reply( $topicWorkflow, $this->user, $this->submitted['content'] );
			$topicPost->setChildren( array( $firstPost ) );
		}


		return array( $topicWorkflow, $topicListEntry, $topicPost, $firstPost );
	}

	/**
	 * Create a new topic attached to the current topic list and write it
	 * out to storage. Additionally generates notifications.
	 */
	public function commit() {
		if ( $this->action !== 'new-topic' ) {
			throw new FailCommitException( 'Unknown commit action', 'fail-commit' );
		}

		$storage = $this->storage;

		$storage->put( $this->topicWorkflow );
		$storage->put( $this->topicListEntry );
		$storage->put( $this->topicPost );
		if ( $this->firstPost !== null ) {
			$storage->put( $this->firstPost );
		}

		$this->notificationController->notifyNewTopic( array(
			'board-workflow' => $this->workflow,
			'topic-workflow' => $this->topicWorkflow,
			'title-post' => $this->topicPost,
			'first-post' => $this->firstPost,
			'user' => $this->user,
		) );

		$notificationController = $this->notificationController;
		$topicWorkflow = $this->topicWorkflow;
		$topicPost = $this->topicPost;
		$output = array(
			'created-topic-id' => $this->topicWorkflow->getId(),
			'created-post-id' => $this->firstPost ? $this->firstPost->getRevisionId() : null,
			'render-function' => function( Templating $templating )
					use ( $topicWorkflow, $topicPost, $storage, $notificationController )
			{
				$block = new TopicBlock( $topicWorkflow, $storage, $notificationController, $topicPost );
				return $templating->renderTopic( $topicPost, $block, true );
			},
		);

		return $output;
	}

	public function render( Templating $templating, array $options ) {
		$templating->getOutput()->addModuleStyles(
			array(
				'ext.flow.base.styles',
				'ext.flow.discussion.styles',
				'ext.flow.moderation.styles',
			)
		);
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

	public function renderAPI( Templating $templating, ApiResult $result, array $options ) {
		$output = array();
		if ( ! $this->workflow->isNew() ) {
			$findOptions = $this->getFindOptions( $options + array( 'api' => true ) );
			$page = $this->getPage( $findOptions );
			$topics = $this->getTopics( $page );

			foreach( $topics as $topic ) {
				$output[] = $topic->renderAPI( $templating, $result, $options );
			}

			$output['paging'] = $page->getPagingLinks();
		}

		$result->setIndexedTagName( $output, 'topic' );

		return $output;
	}

	public function getName() {
		return 'topiclist';
	}

	protected function getLimit( array $options ) {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit;
		$limit = $wgFlowDefaultLimit;
		if ( isset( $options['limit'] ) ) {
			$requestedLimit = intval( $options['limit'] );
			$limit = min( $requestedLimit, $wgFlowMaxLimit );
			$limit = max( 0, $limit );
		}

		return $limit;
	}

	protected function getFindOptions( array $requestOptions ) {
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
	 * @param array $findOptions
	 * @return PagerPage
	 */
	protected function getPage( array $findOptions ) {
		$pager = new Pager(
			$this->storage->getStorage( 'TopicListEntry' ),
			array( 'topic_list_id' => $this->workflow->getId() ),
			$findOptions
		);

		return $pager->getPage();
	}

	/**
	 * @param PagerPage $page
	 * @return TopicBlock[]
	 */
	protected function getTopics( PagerPage $page ) {
		/** @var TopicListEntry[] $found */
		$found = $page->getResults();

		if ( ! count( $found ) ) {
			return array();
		}

		/** @var UUID[] $topicIds */
		$topicIds = array();
		foreach( $found as $entry ) {
			$topicIds[] = $entry->getId();
		}
		$roots = $this->rootLoader->getMulti( $topicIds );
		foreach ( $topicIds as $idx => $topicId ) {
			if ( !$this->permissions->isAllowed( $roots[$topicId->getAlphadecimal()], 'view' ) ) {
				unset( $roots[$topicId->getAlphadecimal()] );
				unset( $topicIds[$idx] );
			}
		}
		foreach ( $roots as $idx => $topicTitle ) {
			if ( !$this->permissions->isAllowed( $topicTitle, 'view' ) ) {
				unset( $roots[$idx] );
			}
		}
		$topics = array();
		foreach ( $this->storage->getMulti( 'Workflow', $topicIds ) as $workflow ) {
			/** @var Workflow $workflow */
			$hexId = $workflow->getId()->getAlphadecimal();
			$topics[$hexId] = $block = new TopicBlock( $workflow, $this->storage, $this->notificationController, $roots[$hexId] );
			$block->init( $this->action, $this->user );
		}

		return $topics;
	}

}

