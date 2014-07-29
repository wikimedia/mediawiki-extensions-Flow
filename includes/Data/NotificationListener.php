<?php

namespace Flow\Data;

use Flow\Model\AbstractRevision;
use Flow\NotificationController;
use User;

class NotificationListener implements LifecycleHandler {

	/**
	 * @var NotificationController
	 */
	protected $notificationController;

	public function __construct( NotificationController $notificationController ) {
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
			) );
			break;

		case 'edit-title':
			$this->notifyPostChange( 'flow-topic-renamed', $object, $metadata );
			break;

		case 'reply':
			$this->notifyPostChange( 'flow-post-reply', $object, $metadata, array(
				'reply-to' => $metadata['reply-to'],
			) );
			break;

		case 'edit-post':
			$this->notifyPostChange( 'flow-post-edited', $object, $metadata );
			break;
		}
	}

	protected function notifyPostChange( $type, $object, $metadata, array $params = array() ) {
		$this->notificationController->notifyPostChange( $type, $params + array(
			'revision' => $object,
			'title' => $metadata['workflow']->getOwnerTitle(),
			'topic-workflow' => $metadata['workflow'],
			'topic-title' => $metadata['topic-title'],
		) );
	}

	public function onAfterLoad( $object, array $row ) {}
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {}
	public function onAfterRemove( $object, array $row, array $metadata ) {}
}
