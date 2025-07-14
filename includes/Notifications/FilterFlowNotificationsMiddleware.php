<?php

namespace Flow\Notifications;

use MediaWiki\Config\Config;
use MediaWiki\Notification\Middleware\FilterMiddleware;
use MediaWiki\Notification\Middleware\FilterMiddlewareAction;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\RecentChanges\RecentChangeNotification;
use MediaWiki\Title\Title;
use MediaWiki\Watchlist\WatchedItemStoreInterface;

/**
 * Abort notifications regarding occupied pages coming from the Watchlist
 * Flow has its own notifications through Echo.
 *
 * Also don't notify for actions made by the talk page manager.
 */
class FilterFlowNotificationsMiddleware extends FilterMiddleware {

	private WatchedItemStoreInterface $watchedItemStore;
	private Config $config;

	public function __construct( Config $config, WatchedItemStoreInterface $watchedItemStore ) {
		$this->config = $config;
		$this->watchedItemStore = $watchedItemStore;
	}

	private function updateWatchlistTimestamp( RecentChangeNotification $notification ) {
		if ( $this->config->get( 'EnotifWatchlist' ) || $this->config->get( 'ShowUpdatedMarker' ) ) {
			$this->watchedItemStore->updateNotificationTimestamp(
				$notification->getAgent(),
				$notification->getTitle(),
				wfTimestampNow()
			);
		}
	}

	protected function filter( NotificationEnvelope $envelope ): FilterMiddlewareAction {
		$notification = $envelope->getNotification();

		if ( $notification instanceof RecentChangeNotification ) {
			$title = Title::newFromPageIdentity( $notification->getTitle() );

			if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
				// Since we are aborting the notification we need to manually update the watchlist
				$this->updateWatchlistTimestamp( $notification );
				return FilterMiddlewareAction::REMOVE;
			}
			if ( $notification->getAgent()->getName() === FLOW_TALK_PAGE_MANAGER_USER ) {
				return FilterMiddlewareAction::REMOVE;
			}
		}
		return FilterMiddlewareAction::KEEP;
	}

}
