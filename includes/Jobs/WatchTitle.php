<?php

namespace Flow\Jobs;

use Flow\Data\DelayedWatchTopicListener;
use Title;
use Job;
use WatchedItem;

class WatchTitle extends Job {
	/**
	 * @param Title $title
	 * @param array $params Array with keys 'board_title' & 'change_type' &
	 *                      their respective values
	 */
	function __construct( Title $title, array $params = array() ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	/**
	 * @return bool
	 */
	public function run() {
		$boardTitle = Title::newFromDBkey( $this->params['board_title'] );
		$changeType = $this->params['change_type'];

		$users = DelayedWatchTopicListener::getUsersToSubscribe( $changeType, 'delayed', array( $boardTitle ) );
		$watchedItems = array();
		foreach ( $users as $user ) {
			$watchedItems[] = WatchedItem::fromUserTitle( $user, $this->title );
		}

		WatchedItem::batchAddWatch( $watchedItems );

		return true;
	}
}
