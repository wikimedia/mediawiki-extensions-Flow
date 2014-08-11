<?php

namespace Flow\Jobs;

use Job;
use Title;
use User;
use WatchedItem;

class WatchTitle extends Job {
	/**
	 * @param Title $title
	 * @param array $params
	 */
	function __construct( Title $title, array $params ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	/**
	 * @return bool
	 */
	function run() {
		$userIds = $this->params['user_ids'];
		foreach ( $userIds as $userId ) {
			$user = User::newFromId( $userId );
			WatchedItem::fromUserTitle( $user, $this->title )->addWatch();
		}

		return true;
	}
}
