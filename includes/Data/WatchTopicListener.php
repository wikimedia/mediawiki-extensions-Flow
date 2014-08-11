<?php

namespace Flow\Data;

use Flow\Jobs\WatchTitle;
use Flow\Model\PostRevision;
use Flow\WatchedTopicItems;
use JobQueueGroup;
use Title;
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
			wfWarn( __METHOD__ . ': Object is no PostRevision instance' );
			return;
		}

		if ( !isset( $metadata['workflow'] ) ) {
			wfWarn( __METHOD__ . ': Missing required metadata: workflow' );
			return;
		}

		if ( $metadata['workflow']->getType() !== 'topic' ) {
			wfWarn( __METHOD__ . ': Expected "topic" workflow but received "' . $metadata['workflow']->getType() . '"' );
			return;
		}

		/** @var $title Title */
		$title = $metadata['workflow']->getArticleTitle();
		if ( !$title ) {
			return;
		}

		$userIds = $this->getUserIdsToSubscribe( $object, $row, $metadata );

		// If current user should watchlist this topic, do so right away!
		$currentUser = $this->watchedTopicItems->getUser();
		if ( in_array( $currentUser->getId(), $userIds ) ) {
			WatchedItem::fromUserTitle( $currentUser, $title )->addWatch();
			$this->watchedTopicItems->addOverrideWatched( $title );

			// Current user has been dealt with, let's get it out of the
			// array of users that will be inserted via a job.
			$key = array_search( $currentUser->getId(), $userIds );
			unset( $userIds[$key] );
		}

		// Defer all other users to a job
		if ( $userIds ) {
			$job = new WatchTitle(
				$title,
				array( 'user_ids' => $userIds )
			);

			JobQueueGroup::singleton()->push( $job );
		}
	}

	/**
	 * Returns an array of user ids to subscribe to the title.
	 *
	 * @param PostRevision $object
	 * @param array $row
	 * @param array $metadata
	 * @return int[]
	 */
	protected function getUserIdsToSubscribe( $object, $row, $metadata ) {
		// Find this particular action & the users it'll make the title watch
		if ( !isset( $this->changeTypes[$row['rev_change_type']] ) ) {
			return array();
		}
		$userIds = $this->changeTypes[$row['rev_change_type']];

		// Some actions may have more complex logic to determine watching users
		if ( $userIds instanceof \Closure ) {
			$userIds = $userIds( $object, $row, $metadata );
		} elseif ( is_callable( $userIds ) ) {
			$userIds = call_user_func( $userIds, $object, $row, $metadata );
		}

		return $userIds;
	}

	/**
	 * @param PostRevision $object
	 * @param array $row
	 * @param array $metadata
	 * @return int[]
	 */
	public static function getUserIdsWatchingBoard( $object, $row, $metadata ) {
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

		$userIds = array();
		foreach ( $res as $user ) {
			$userIds[] = $user->wl_user;
		}

		return $userIds;
	}

	// do nothing
	public function onAfterLoad( $object, array $new ) {}
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {}
	public function onAfterRemove( $object, array $old, array $metadata ) {}
}
