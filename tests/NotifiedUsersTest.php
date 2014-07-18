<?php
namespace Flow\Tests;

use Flow\Container;
use Flow\NotificationController;
use User;
use WatchedItem;

class NotifiedUsersTest extends PostRevisionTestCase {
	public function setUp() {
		parent::setUp();

		global $wgFlowOccupyPages;

		$wgFlowOccupyPages[] = 'User talk:Flow';
	}
	public function testWatchingTopic() {
		extract( $this->getTestData() );

		$notificationController->subscribeToWorkflow( $user, $topicWorkflow );

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
		extract( $this->getTestData() );

		$notificationController->subscribeToWorkflow( $user, $boardWorkflow );

		$events = $notificationController->notifyNewTopic( array(
			'board-workflow' => $boardWorkflow,
			'topic-workflow' => $topicWorkflow,
			'title-post' => $topic,
			'first-post' => $post,
			'user' => $agent,
		) );

		$this->assertNotifiedUser( $events, $user, $agent );
	}

	protected function assertNotifiedUser( array $events, User $notifiedUser, User $notNotifiedUser ) {
		$users = array();
		foreach( $events as $event ) {
			NotificationController::getDefaultNotifiedUsers( $event, $users );
		}

		$this->assertArrayHasKey( $notifiedUser->getId(), $users );
		$this->assertArrayNotHasKey( $notNotifiedUser->getId(), $users );
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

		$boardWorkflow = Container::get( 'factory.loader.workflow' )
			->createWorkflowLoader( $topicWorkflow->getOwnerTitle() )
			->getWorkflow();

		Container::get( 'storage' )->put( $boardWorkflow );

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