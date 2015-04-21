<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;
use Flow\NotificationController;
use Flow\OccupationController;
use Flow\Data\ManagerGroup;
use EchoNotificationController;
use User;
use WatchedItem;

/**
 * @group Flow
 */
class NotifiedUsersTest extends PostRevisionTestCase {
	protected $tablesUsed = array(
		'echo_event',
		'echo_notification',
		'flow_revision',
		'flow_topic_list',
		'flow_tree_node',
		'flow_tree_revision',
		'flow_workflow',
		'page',
		'revision',
		'text',
	);

	public function setUp() {
		parent::setUp();

		if ( !class_exists( 'EchoEvent' ) ) {
			$this->markTestSkipped();
			return;
		}
	}
	public function testWatchingTopic() {
		$data = $this->getTestData();
		if ( !$data ) {
			$this->markTestSkipped();
			return;
		}

		WatchedItem::fromUserTitle( $data['user'], $data['topicWorkflow']->getArticleTitle() )->addWatch();

		$events = $data['notificationController']->notifyPostChange( 'flow-post-reply',
			array(
				'topic-workflow' => $data['topicWorkflow'],
				'title' => $data['boardWorkflow']->getOwnerTitle(),
				'user' => $data['agent'],
				'reply-to' => $data['topic'],
				'topic-title' => $data['topic'],
				'revision' => $data['post'],
			) );

		$this->assertNotifiedUser( $events, $data['user'], $data['agent'] );
	}

	public function testWatchingBoard() {
		$data = $this->getTestData();
		if ( !$data ) {
			$this->markTestSkipped();
			return;
		}

		WatchedItem::fromUserTitle( $data['user'], $data['boardWorkflow']->getArticleTitle() )->addWatch();

		$events = $data['notificationController']->notifyNewTopic( array(
			'board-workflow' => $data['boardWorkflow'],
			'topic-workflow' => $data['topicWorkflow'],
			'topic-title' => $data['topic'],
			'first-post' => $data['post'],
			'user' => $data['agent'],
		) );

		$this->assertNotifiedUser( $events, $data['user'], $data['agent'] );
	}

	protected function assertNotifiedUser( array $events, User $notifiedUser, User $notNotifiedUser ) {
		$users = array();
		foreach( $events as $event ) {
			$iterator = EchoNotificationController::getUsersToNotifyForEvent( $event );
			foreach( $iterator as $user ) {
				$users[] = $user;
			}
		}

		// convert user objects back into user ids to simplify assertion
		$users = array_map( function( $user ) { return $user->getId(); }, $users );

		$this->assertContains( $notifiedUser->getId(), $users );
		$this->assertNotContains( $notNotifiedUser->getId(), $users );
	}

	/**
	 * @return bool|array
	 * {
	 *     False on failure, or array with these keys:
	 *
	 *     @type Workflow $boardWorkflow
	 *     @type Workflow $topicWorkflow
	 *     @type PostRevision $post
	 *     @type PostRevision $topic
	 *     @type User $user
	 *     @type User $agent
	 *     @type NotificationController $notificationController
	 * }
	 */
	protected function getTestData() {
		$user = User::newFromName( 'Flow Test User' );
		$user->addToDatabase();
		$agent = User::newFromName( 'Flow Test Agent' );
		$agent->addToDatabase();

		$notificationController = Container::get( 'controller.notification' );

		$title = \Title::newFromText( 'Talk:Notification_test' );
		$boardWorkflow = Workflow::create( 'discussion', $title );
		$topicWorkflow = Workflow::create( 'topic', $boardWorkflow->getArticleTitle() );
		$topicList = TopicListEntry::create( $boardWorkflow, $topicWorkflow );
		$topicTitle = PostRevision::create( $topicWorkflow, $agent, 'some content', 'wikitext' );
		$firstPost = $topicTitle->reply( $boardWorkflow, $agent, 'ffuts dna ylper', 'wikitext' );

		/*
		 * We don't really *have* to store everything for this test. We could
		 * just work off of the object we have here.
		 * However, our current CI setup forces use to not use Parsoid & write
		 * wikitext instead.
		 * Notifications need to convert the content to HTML & in order to do so
		 * have to know the title of the board the post is on (to resolve links
		 * & stuff).
		 * For those combined reasons, we'll store everything.
		 *
		 * @var OccupationController $occupationController
		 * @var ManagerGroup $storage
		 */
		$occupationController = Container::get( 'occupation_controller' );
		$storage = Container::get( 'storage' );

		// make sure user has rights to create board
		$agent->mRights = array_merge( $agent->getRights(), array( 'flow-create-board' ) );
		$occupationController->allowCreation( $title, $agent );
		$occupationController->ensureFlowRevision( new \Article( $title ), $boardWorkflow );

		$storage->put( $boardWorkflow, array( 'workflow' => $boardWorkflow ) );
		$storage->put( $topicWorkflow, array( 'workflow' => $topicWorkflow ) );
		$storage->put( $topicList, array( 'workflow' => $topicWorkflow ) );
		$storage->put( $topicTitle, array( 'workflow' => $topicWorkflow ) );
		$storage->put( $firstPost, array( 'workflow' => $topicWorkflow ) );

		return array(
			'boardWorkflow' => $boardWorkflow,
			'topicWorkflow' => $topicWorkflow,
			'post' => $firstPost,
			'topic' => $topicTitle,
			'user' => $user,
			'agent' => $agent,
			'notificationController' => $notificationController,
		);
	}
}
