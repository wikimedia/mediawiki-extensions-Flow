<?php

namespace Flow\Import\Postprocessor;

use DatabaseBase;
use EchoBatchRowIterator;
use EchoCallbackIterator;
use EchoEvent;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
use Flow\Import\LiquidThreadsApi\ImportTopic as LqtImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;
use Flow\Model\UUID;
use Flow\NotificationController;
use RecursiveIteratorIterator;
use User;

/**
 * Converts LQT unread notifications into Echo notifications after a topic is imported
 */
class LqtNotifications implements Postprocessor {

	/**
	 * @var NotificationController
	 */
	protected $controller;

	/**
	 * @var DatabaseBase
	 */
	protected $dbw;

	/**
	 * @var bool True when posts have been imported for the current topic
	 */
	protected $postsImported = false;

	public function __construct( NotificationController $controller, DatabaseBase $dbw ) {
		$this->controller = $controller;
		$this->dbw = $dbw;
		$this->overrideUsersToNotify();
	}

	/**
	 * evil, but what should we do instead? This basically removes the default methods
	 * of determining users to notify so they can be replaced with this class during imports.
	 */
	protected function overrideUsersToNotify() {
		global $wgHooks, $wgEchoNotifications;

		// Remove the hook subscriber that chooses users for some notifications
		$idx = array_search(
			'Flow\NotificationController::getDefaultNotifiedUsers',
			$wgHooks['EchoGetDefaultNotifiedUsers']
		);
		if ( $idx !== false ) {
			unset( $wgHooks['EchoGetDefaultNotifiedUsers'][$idx] );
		}


		// Remove the user-locators that choose on a per-notification basis who
		// should be notified.
		$notifications = require __DIR__ . '/../../Notifications/Notifications.php';
		foreach ( array_keys( $notifications ) as $type ) {
			unset( $wgEchoNotifications[$type]['user-locators'] );
		}

		// Insert our own user locator to decide who should be notified.
		// Note this has to be a closure rather than direct callback due to how
		// echo considers an array to be extra parameters.
		// Overrides existing user-locators, because we don't want unintended
		// notifications to go out here.
		$self = $this;
		$wgEchoNotifications['flow-post-reply']['user-locators'] = array(
			function( EchoEvent $event ) use ( $self ) {
				return $self->locateUsersWithPendingLqtNotifications( $event );
			}
		);
	}

	/**
	 * @param EchoEvent $event
	 * @param int $batchSize
	 * @throws ImportException
	 * @return \Iterator[User]
	 */
	public function locateUsersWithPendingLqtNotifications( EchoEvent $event, $batchSize = 500 ) {
		$activeThreadId = $event->getExtraParam( 'lqtThreadId' );
		if ( $activeThreadId === null ) {
			throw new ImportException( 'No active thread!' );
		}

		$it = new EchoBatchRowIterator(
			$this->dbw,
			/* table = */ 'user_message_state',
			/* primary keys */ array( 'ums_user' ),
			$batchSize
		);
		$it->addConditions( array(
			'ums_conversation' => $activeThreadId,
			'ums_read_timestamp' => null,
		) );

		// flatten result into a stream of rows
		$it = new RecursiveIteratorIterator( $it );

		// add callback to convert user id to user objects
		$it = new EchoCallbackIterator( $it, function( $row ) {
			return User::newFromId( $row->ums_user );
		} );

		return $it;
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		if ( !$topic instanceof LqtImportTopic ) {
			return;
		}
		if ( $this->postsImported === false ) {
			// nothing was imported in this topic
			return;
		}

		$this->postsImported = false;
		$this->controller->notifyPostChange( 'flow-post-reply', array(
			'revision' => $state->topicTitle,
			'topic-title' => $state->topicTitle,
			'topic-workflow' => $state->topicWorkflow,
			'title' => $state->topicWorkflow->getOwnerTitle(),
			'reply-to' => $state->topicTitle,
			'extra-data' => array(
				'lqtThreadId' => $topic->getLqtThreadId(),
				'notifyAgent' => true,
			),
		) );
	}

	public function importAborted() {
		$this->postsImported = false;
	}

	public function afterHeaderImported( PageImportState $state, IImportHeader $header ) {
		// not a thing to do, yet
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, UUID $newPostId ) {
		$this->postsImported = true;
	}

}
