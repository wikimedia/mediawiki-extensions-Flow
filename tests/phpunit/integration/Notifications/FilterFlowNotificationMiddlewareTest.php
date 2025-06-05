<?php

namespace Flow\Tests\Notifications;

use Flow\Notifications\FilterFlowNotificationsMiddleware;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\Notification\NotificationsBatch;
use MediaWiki\Notification\RecipientSet;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentityValue;
use MediaWiki\Watchlist\RecentChangeNotification;
use MediaWiki\Watchlist\WatchedItemStoreInterface;

/**
 * @covers \Flow\Notifications\FilterFlowNotificationsMiddleware
 *
 * @group Flow
 * @group Database
 */
class FilterFlowNotificationMiddlewareTest extends \MediaWikiIntegrationTestCase {

	public function testRemovesNotificationsTriggeredByFlowTalkPageManager() {
		$title = Title::makeTitle( NS_MAIN, 'Foobar' );
		$someUser = UserIdentityValue::newRegistered( 1, 'Test user' );
		$manager = UserIdentityValue::newRegistered( 2, FLOW_TALK_PAGE_MANAGER_USER );
		$recipient = UserIdentityValue::newRegistered( 3, 'Recipient' );

		$rc1 = $this->createMock( RecentChange::class );
		$rc2 = $this->createMock( RecentChange::class );

		$first = new RecentChangeNotification( $someUser, $title, $rc1, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);
		$second = new RecentChangeNotification( $manager, $title, $rc2, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);

		$batch = new NotificationsBatch(
			new NotificationEnvelope( $first, new RecipientSet( [ $recipient ] ) ),
			new NotificationEnvelope( $second, new RecipientSet( [ $recipient ] ) ),
		);

		$middleware = new FilterFlowNotificationsMiddleware(
			$this->getServiceContainer()->getMainConfig(),
			$this->getServiceContainer()->getWatchedItemStore()
		);
		$middleware->handle( $batch, static fn () => true );
		/** @var NotificationEnvelope<RecentChangeNotification>[] $notifications */
		$notifications = iterator_to_array( $batch );

		$this->assertCount( 1, $notifications );
		$this->assertSame( $notifications[0]->getNotification()->getRecentChange(), $rc1 );
	}

	public function testBumpsNotificationTimestampsAndFiltersForFlowBoardContentModel() {
		$title = Title::makeTitle( NS_MAIN, 'Foobar' );
		$title->setContentModel( CONTENT_MODEL_FLOW_BOARD );

		$someUser = UserIdentityValue::newRegistered( 1, 'Test user' );
		$recipient = UserIdentityValue::newRegistered( 3, 'Recipient' );

		$rc = $this->createMock( RecentChange::class );
		$storeMock = $this->createMock( WatchedItemStoreInterface::class );
		$storeMock->expects( $this->once() )
			->method( 'updateNotificationTimestamp' )
			->willReturnCallback(
				function ( $agent, $watchedTitle, $timestamp ) use ( $someUser, $title ) {
					$this->assertSame( $someUser, $agent );
					$this->assertSame( $title, $watchedTitle );
					$this->assertNotEmpty( $timestamp );
				} );

		$shouldBeRemoved = new RecentChangeNotification( $someUser, $title, $rc, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);

		$batch = new NotificationsBatch(
			new NotificationEnvelope( $shouldBeRemoved, new RecipientSet( [ $recipient ] ) ),
		);

		$middleware = new FilterFlowNotificationsMiddleware(
			$this->getServiceContainer()->getMainConfig(), $storeMock
		);
		$middleware->handle( $batch, static fn () => true );

		$this->assertCount( 0, $batch );
	}

	public function testDoesNothingForArticleNotification() {
		$title = Title::makeTitle( NS_MAIN, 'Foobar' );

		$someUser = UserIdentityValue::newRegistered( 1, 'Test user' );
		$recipient = UserIdentityValue::newRegistered( 3, 'Recipient' );
		$rc = $this->createMock( RecentChange::class );

		$storeMock = $this->createMock( WatchedItemStoreInterface::class );
		$storeMock->expects( $this->never() )
			->method( 'updateNotificationTimestamp' );

		$notification = new RecentChangeNotification( $someUser, $title, $rc, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);

		$batch = new NotificationsBatch(
			new NotificationEnvelope( $notification, new RecipientSet( [ $recipient ] ) ),
		);

		$middleware = new FilterFlowNotificationsMiddleware(
			$this->getServiceContainer()->getMainConfig(), $storeMock
		);
		$middleware->handle( $batch, static fn () => true );

		$this->assertCount( 1, $batch );
	}
}
