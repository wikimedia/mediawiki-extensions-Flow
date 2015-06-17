<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\NotificationController;
use EchoNotificationController;
use User;
use WatchedItem;

/**
 * @group Flow
 */
class NotifiedUsersTest extends PostRevisionTestCase {
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
		$topicWorkflow = $this->generateWorkflow();
		$post = $this->generateObject( array(), array(), 1 );
		$topic = $this->generateObject( array(), array( $post ) );
		$user = User::newFromName( 'Flow Test User' );
		$user->addToDatabase();
		$agent = User::newFromName( 'Flow Test Agent' );
		$agent->addToDatabase();

		$notificationController = Container::get( 'controller.notification' );

		// The data of this global varaible is loaded into occupationListener
		// even before the test starts, so modifying this global in setUP()
		// won't have any effect on the occupationListener.  The trick is to
		// fake the workflow to have a title in the global varaible
		global $wgFlowOccupyPages;

		$page = reset( $wgFlowOccupyPages );
		if ( !$page ) {
			return false;
		}
		$title = \Title::newFromText( $page );
		if ( !$title ) {
			return false;
		}
		$object = new \ReflectionObject( $topicWorkflow );
		$ownerTitle = $object->getProperty( 'ownerTitle' );
		$ownerTitle->setAccessible( true );
		$ownerTitle->setValue( $topicWorkflow, $title );

		$boardWorkflow = Container::get( 'factory.loader.workflow' )
			->createWorkflowLoader( $topicWorkflow->getOwnerTitle() )
			->getWorkflow();

		return array(
			'boardWorkflow' => $boardWorkflow,
			'topicWorkflow' => $topicWorkflow,
			'post' => $post,
			'topic' => $topic,
			'user' => $user,
			'agent' => $agent,
			'notificationController' => $notificationController,
		);
	}
}
