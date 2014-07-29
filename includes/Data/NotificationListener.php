<?php

namespace Flow\Data;

use Flow\Model\AbstractRevision;
use Flow\NotificationController;
use User;

class NotificationListener implements LifecycleHandler {

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var NotificationController
	 */
	protected $notificationController;

	public function __construct( User $user, NotificationController $notificationController ) {
		$this->user = $user;
		$this->notificationController = $notificationController;
	}

	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !$object instanceof AbstractRevision ) {
			return;
		}
		$type = null;
		$params = array();
		switch( $row['rev_change_type'] ) {
		// Actually new-topic @todo rename
		case 'new-post':
			$this->notificationController->notifyNewTopic( array(
				// @todo this seems like a fragile way to pass these parameters,
				// but works for now.
				'board-workflow' => $metadata['board-workflow'],
				'topic-workflow' => $metadata['workflow'],
				'topic-title' => $metadata['topic-title'],
				'first-post' => $metadata['first-post'],
				'user' => $this->user,
			) );
			break;

		case 'edit-title':
			$type = 'flow-topic-renamed';
			break;

		case 'reply':
			$type = 'flow-post-reply';
			$params['reply-to'] = $metadata['reply-to'];
			break;

		case 'edit-post':
			$type = 'flow-post-edited';
			break;
		}

		if ( $type !== null ) {
			$this->notificationController->notifyPostChange( $type, $params + array(
				'revision' => $object,
				'title' => $metadata['workflow']->getOwnerTitle(),
				'topic-workflow' => $metadata['workflow'],
				'topic-title' => $metadata['topic-title'],
				'user' => $this->user,
			) );
		}
	}

	public function onAfterLoad( $object, array $row ) {}
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {}
	public function onAfterRemove( $object, array $row, array $metadata ) {}
}
