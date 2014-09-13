<?php

namespace Flow;

use EchoEvent;
use EchoUserLocator;
use Flow\Model\UUID;
use Title;
use User;

class NotificationsUserLocator extends EchoUserLocator {
	/**
	 * Return all users watching the topic the event was for.
	 *
	 * The echo job queue must be enabled to prevent timeouts submitting to
	 * heavily watched pages when this is used.
	 *
	 * @param EchoEvent $event
	 * @return User[]
	 */
	public static function locateUsersWatchingTopic( EchoEvent $event ) {
		$workflowId = $event->getExtraParam( 'topic-workflow' );
		if ( !$workflowId instanceof UUID ) {
			// something wrong; don't notify anyone
			return array();
		}

		// topic title is just the workflow id, but in NS_TOPIC
		$title = Title::makeTitleSafe( NS_TOPIC, $workflowId->getAlphadecimal() );

		/*
		 * Override title associated with this event. The existing code to
		 * locate users watching something uses the title associated with the
		 * event, which in this case is the board page.
		 * However, here, we're looking to get users who've watchlisted a
		 * specific NS_TOPIC page.
		 * I'm temporarily substituting the event's title so we can piggyback on
		 * locateUsersWatchingTitle instead of duplicating it.
		 */
		$originalTitle = $event->getTitle();
		$event->setTitle( $title );

		$users = parent::locateUsersWatchingTitle( $event );

		// reset original title
		$event->setTitle( $originalTitle );

		return $users;
	}
}
