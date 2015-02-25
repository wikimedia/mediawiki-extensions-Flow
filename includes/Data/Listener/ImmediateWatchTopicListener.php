<?php

namespace Flow\Data\Listener;

use Flow\Container;
use Flow\Data\LifecycleHandler;
use Flow\Exception\InvalidDataException;
use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\WatchedTopicItems;
use Title;
use User;
use WatchedItem;

/**
 * Class to immediately subscribe users to the article title when one of the
 * actions specified in the constructor is inserted.
 */
class ImmediateWatchTopicListener extends AbstractTopicInsertListener {
	/**
	 * @var WatchedTopicItems
	 */
	protected $watchedTopicItems;

	/**
	 * @param WatchedTopicItems $watchedTopicItems Helper class for watching titles
	 */
	public function __construct( WatchedTopicItems $watchedTopicItems ) {
		$this->watchedTopicItems = $watchedTopicItems;
	}

	/**
	 * @param string $changeType
	 * @param Workflow $workflow
	 */
	public function onAfterInsertExpectedChange( $changeType, Workflow $workflow ) {
		$users = static::getUsersToSubscribe( $changeType, 'immediate', array( $this->watchedTopicItems ) );

		foreach ( $users as $user ) {
			if ( !$user instanceof User ) {
				continue;
			}
			$title = $workflow->getArticleTitle();

			WatchedItem::fromUserTitle( $user, $title )->addWatch();
			$this->watchedTopicItems->addOverrideWatched( $title );
		}
	}

	/**
	 * @param WatchedTopicItems $watchedTopicItems
	 * @return User[]
	 */
	public static function getCurrentUser( WatchedTopicItems $watchedTopicItems ) {
		return array( $watchedTopicItems->getUser() );
	}
}
