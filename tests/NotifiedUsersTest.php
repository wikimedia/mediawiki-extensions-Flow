<?php
namespace Flow\Tests;

use Flow\Container;
use Flow\NotificationController;
use EchoNotificationController;
use User;
use WatchedItem;

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
		extract( $data );

		WatchedItem::fromUserTitle( $user, $topicWorkflow->getArticleTitle() )->addWatch();

		$events = $notificationController->notifyPostChange( 'flow-post-reply',
			array(
				'topic-workflow' => $topicWorkflow,
				'title' => $boardWorkflow->getOwnerTitle(),
				'user' => $agent,
				'reply-to' => $topic,
				'topic-title' => $topic,
				'revision' => $post,
			) );

		$this->assertNotifiedUser( $events, $user, $agent );
	}

	public function testWatchingBoard() {
		$data = $this->getTestData();
		if ( !$data ) {
			$this->markTestSkipped();
			return;
		}
		extract( $data );

		WatchedItem::fromUserTitle( $user, $boardWorkflow->getArticleTitle() )->addWatch();

		$events = $notificationController->notifyNewTopic( array(
			'board-workflow' => $boardWorkflow,
			'topic-workflow' => $topicWorkflow,
			'topic-title' => $topic,
			'first-post' => $post,
			'user' => $agent,
		) );

		$this->assertNotifiedUser( $events, $user, $agent );
	}

	protected function assertNotifiedUser( array $events, User $notifiedUser, User $notNotifiedUser ) {
		$users = array();
		foreach( $events as $event ) {
			$users = array_merge(
				$users,
				EchoNotificationController::getUsersToNotifyForEvent( $event )
			);
		}

		// convert user objects back into user ids to simplify assertion
		$users = array_map( function( $user ) { return $user->getId(); }, $users );

		$this->assertContains( $notifiedUser->getId(), $users );
		$this->assertNotContains( $notNotifiedUser->getId(), $users );
	}

	protected function getTestData() {
		$this->generateWorkflowForPost();
		$topicWorkflow = $this->workflow;
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

		return compact( array(
				'boardWorkflow',
				'topicWorkflow',
				'post',
				'topic',
				'user',
				'agent',
				'notificationController',
			) );
	}
}
