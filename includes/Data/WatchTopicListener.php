<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\WatchedTopicItems;
use User;
use WatchedItem;

/**
 * Auto-watch topics when the user performs one of the actions specified
 * in the constructor.
 */
class WatchTopicListener {
	/**
	 * @var WatchedTopicItems
	 */
	protected $watchedTopicItems;

	/**
	 * @var array List of revision change types to trigger watchlist on
	 */
	protected $changeTypes;

	/**
	 * @param WatchedTopicItems $watchedTopicItems Helper class for watching titles
	 * @param array $changeTypes List of revision change types to trigger watch on
	 */
	public function __construct( WatchedTopicItems $watchedTopicItems, array $changeTypes ) {
		$this->watchedTopicItems = $watchedTopicItems;
		$this->changeTypes = $changeTypes;
	}

	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !$object instanceof PostRevision ) {
			// @todo should this be an exception?
			return;
		}

		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}

		if ( $metadata['workflow']->getType() !== 'topic' ) {
			throw new FlowException( 'Expected `topic` workflow but received `' . $metadata['workflow']->getType() . '`' );
		}

		$title = $metadata['workflow']->getArticleTitle();
		if ( !$title ) {
			return;
		}

		// Subscribe all given users to this Title
		$users = $this->getUsersToSubscribe( $object, $row, $metadata );
		foreach ( $users as $user ) {
			WatchedItem::fromUserTitle( $user, $title )->addWatch();

			/*
			 * WatchedTopicItems is some helper object that holds all of the
			 * current user's watched topics. We should also update the watch
			 * status in that object, if this user was subscribed to the Title.
			 */
			if ( $this->watchedTopicItems->getUser() === $user ) {
				$this->watchedTopicItems->addOverrideWatched( $title );
			}
		}
	}

	/**
	 * Returns an array of user ids to subscribe to the title.
	 *
	 * @param PostRevision $object
	 * @param array $row
	 * @param array $metadata
	 * @return User[]
	 */
	protected function getUsersToSubscribe( $object, $row, $metadata ) {
		// Find this particular action & the users it'll make the title watch
		if ( !isset( $this->changeTypes[$row['rev_change_type']] ) ) {
			return array();
		}
		$users = $this->changeTypes[$row['rev_change_type']];

		// Some actions may have more complex logic to determine watching users
		if ( $users instanceof \Closure ) {
			$users = $users( $object, $row, $metadata );
		} elseif ( is_callable( $users ) ) {
			$users = call_user_func( $users, $object, $row, $metadata );
		}

		return ObjectManager::makeArray( $users );
	}

	/**
	 * @param PostRevision $object
	 * @param array $row
	 * @param array $metadata
	 * @return User[]
	 */
	public static function getUsersWatchingBoard( $object, $row, $metadata ) {
		$dbr = wfGetDB( DB_SLAVE, 'watchlist' );
		$boardTitle = $metadata['workflow']->getOwnerTitle();

		// Get users already watching this board, they should be
		// auto-subscribed to the new topic.
		$res = $dbr->select(
			array( 'watchlist' ),
			array( 'wl_user' ),
			array(
				'wl_namespace' => $boardTitle->getNamespace(),
				'wl_title' => $boardTitle->getDBkey()
			),
			__METHOD__
		);

		if ( !$res ) {
			return array();
		}

		$users = array();
		foreach ( $res as $user ) {
			$users[] = \User::newFromId( $user->wl_user );
		}

		return $users;
	}

	// do nothing
	public function onAfterLoad( $object, array $new ) {}
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {}
	public function onAfterRemove( $object, array $old, array $metadata ) {}
}
