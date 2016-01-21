<?php

namespace Flow;

use EchoEvent;
use EchoUserLocator;
use Flow\Data\ManagerGroup;
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

	/**
	 * @param EchoEvent $event
	 * @return User[]
	 */
	public static function locateTopicAuthors( EchoEvent $event ) {
		$workflowId = $event->getExtraParam( 'topic-workflow' );
		if ( !$workflowId instanceof UUID ) {
			// something wrong; don't notify anyone
			return array();
		}

		return self::getCreatorsFromPostIDs( array( $workflowId ) );
	}

	/**
	 * @param EchoEvent $event
	 * @return User[]
	 */
	public static function locatePostAuthors( EchoEvent $event ) {
		$extra = $event->getExtra();

		if ( isset( $extra['reply-to'] ) ) {
			$postId = $extra['reply-to'];
		} else {
			$postId = $extra['post-id'];
		}

		if ( !$postId instanceof UUID ) {
			// something wrong; don't notify anyone
			return array();
		}

		return self::getCreatorsFromPostIDs( array( $postId ) );
	}

	/**
	 * @param EchoEvent $event
	 * @return array
	 */
	public static function locateMentionedUsers( EchoEvent $event ) {
		$mentionedUserIds = $event->getExtraParam( 'mentioned-users' );
		if ( !$mentionedUserIds ) {
			return array();
		}

		// figure out which users may already receive a notification for this event because they're author,
		// or because they're watching this topic
		$notifiedUsers = array();
		$locators = array( self::locatePostAuthors( $event ), self::locateUsersWatchingTopic( $event ) );
		foreach ( $locators as $locator ) {
			/** @var User $user */
			foreach ( $locator as $user ) {
				$notifiedUsers[$user->getId()] = $user;
			}
		}

		// now subtract those that already receive another notification
		$mentionedUserIds = array_diff( $mentionedUserIds, array_keys( $notifiedUsers ) );

		return array_map( array( 'User', 'newFromId' ), $mentionedUserIds );
	}

	/**
	 * Retrieves the post creators from a set of posts.
	 *
	 * @param array $posts Array of UUIDs or hex representations
	 * @return User[] Associative array, of user ID => User object.
	 */
	protected static function getCreatorsFromPostIDs( array $posts ) {
		$users = array();
		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );

		$user = new User;
		$actionPermissions = new RevisionActionPermissions( Container::get( 'flow_actions' ), $user );

		foreach ( $posts as $postId ) {
			$post = $storage->find(
				'PostRevision',
				array(
					'rev_type_id' => UUID::create( $postId )
				),
				array(
					'sort' => 'rev_id',
					'order' => 'DESC',
					'limit' => 1
				)
			);

			$post = reset( $post );

			if ( $post && $actionPermissions->isAllowed( $post, 'view' ) ) {
				$userid = $post->getCreatorId();
				if ( $userid ) {
					$users[$userid] = User::newFromId( $userid );
				}
			}
		}

		return $users;
	}
}
