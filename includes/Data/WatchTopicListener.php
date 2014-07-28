<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\WatchedTopicItems;
use User;
use WatchedItem;

class WatchTopicListener {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var WatchedTopicItems
	 */
	protected $watchedTopicItems;

	/**
	 * @var array List of revision change types to trigger watchlist on
	 */
	protected $changeTypes;

	/**
	 * @param User $user The user that will be subscribed to workflows
	 * @param WatchedTopicItems $watchedTopicItems Helper class for watching titles
	 * @param array $changeTypes List of revision change types to trigger watch on
	 */
	public function __construct( User $user, WatchedTopicItems $watchedTopicItems, array $changeTypes ) {
		$this->user = $user;
		$this->watchedTopicItems = $watchedTopicItems;
		$this->changeTypes = $changeTypes;
	}

	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !$object instanceof PostRevision ) {
			// @todo should this be an exception?
			return;
		}
		if ( !in_array( $row['rev_change_type'], $this->changeTypes ) ) {
			return;
		}
		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}
		if ( $metadata['workflow']->getType() !== 'topic' ) {
			throw new FlowException( 'Expected `topic` workflow but received `' . $metadata['workflow']->getType() . '`' );
		}


		$title = $metadata['workflow']->getArticleTitle();
		if ( $title ) {
			WatchedItem::fromUserTitle( $this->user, $title )
				->addWatch();
			$this->watchedTopicItems->addOverrideWatched( $title );
		}
	}

	// do nothing
	public function onAfterLoad( $object, array $new ) {}
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {}
	public function onAfterRemove( $object, array $old, array $metadata ) {}
}
