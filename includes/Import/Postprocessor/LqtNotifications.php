<?php

namespace Flow\Import\Postprocessor;

use BatchRowIterator;
use Flow\Import\IImportHeader;
use Flow\Import\IImportPost;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
use Flow\Import\LiquidThreadsApi\ImportTopic;
use Flow\Import\PageImportState;
use Flow\Import\TopicImportState;
use Flow\Model\PostRevision;
use Flow\Notifications\Controller;
use MediaWiki\Extension\Notifications\Iterator\CallbackIterator;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\User\User;
use RecursiveIteratorIterator;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Converts LQT unread notifications into Echo notifications after a topic is imported
 */
class LqtNotifications implements Postprocessor {

	/**
	 * @var Controller
	 */
	protected $controller;

	protected IReadableDatabase $dbr;

	/**
	 * @var PostRevision[] Array of imported replies
	 */
	protected $postsImported = [];

	public function __construct( Controller $controller, IReadableDatabase $dbr ) {
		$this->controller = $controller;
		$this->dbr = $dbr;
		$this->overrideUsersToNotify();
	}

	/**
	 * evil, but what should we do instead? This basically removes the default methods
	 * of determining users to notify so they can be replaced with this class during imports.
	 */
	protected function overrideUsersToNotify() {
		global $wgEchoNotifications;

		// Remove the user-locators that choose on a per-notification basis who
		// should be notified.
		$notifications = require dirname( dirname( dirname( __DIR__ ) ) ) . '/Notifications.php';
		foreach ( array_keys( $notifications ) as $type ) {
			unset( $wgEchoNotifications[$type]['user-locators'] );

			// The job queue causes our overrides to be lost since it
			// has a separate execution context.
			$wgEchoNotifications[$type]['immediate'] = true;
		}

		// Insert our own user locator to decide who should be notified.
		// Note this has to be a closure rather than direct callback due to how
		// echo considers an array to be extra parameters.
		// Overrides existing user-locators, because we don't want unintended
		// notifications to go out here.
		$wgEchoNotifications['flow-post-reply']['user-locators'] = [
			function ( Event $event ) {
				return $this->locateUsersWithPendingLqtNotifications( $event );
			}
		];
	}

	/**
	 * @param Event $event
	 * @param int $batchSize
	 * @throws ImportException
	 * @return CallbackIterator
	 */
	public function locateUsersWithPendingLqtNotifications( Event $event, $batchSize = 500 ) {
		$activeThreadId = $event->getExtraParam( 'lqtThreadId' );
		if ( $activeThreadId === null ) {
			throw new ImportException( 'No active thread!' );
		}

		$it = new BatchRowIterator(
			$this->dbr,
			/* table = */ 'user_message_state',
			/* primary keys */ [ 'ums_user' ],
			$batchSize
		);
		$it->addConditions( [
			'ums_conversation' => $activeThreadId,
			'ums_read_timestamp' => null,
		] );
		$it->setCaller( __METHOD__ );

		// flatten result into a stream of rows
		$it = new RecursiveIteratorIterator( $it );

		// add callback to convert user id to user objects
		$it = new CallbackIterator( $it, static function ( $row ) {
			return User::newFromId( $row->ums_user );
		} );

		return $it;
	}

	public function afterTopicImported( TopicImportState $state, IImportTopic $topic ) {
		if ( !$topic instanceof ImportTopic ) {
			return;
		}
		if ( !$this->postsImported ) {
			// nothing was imported in this topic
			return;
		}

		$this->controller->notifyPostChange( 'flow-post-reply', [
			'revision' => $this->postsImported[0],
			'topic-title' => $state->topicTitle,
			'topic-workflow' => $state->topicWorkflow,
			'title' => $state->topicWorkflow->getOwnerTitle(),
			'reply-to' => $state->topicTitle,
			'extra-data' => [
				'lqtThreadId' => $topic->getLqtThreadId(),
				'notifyAgent' => true,
			],
			'timestamp' => $topic->getTimestamp(),
		] );

		$this->postsImported = [];
	}

	public function importAborted() {
		$this->postsImported = [];
	}

	public function afterHeaderImported( PageImportState $state, IImportHeader $header ) {
		// not a thing to do, yet
	}

	public function afterPostImported( TopicImportState $state, IImportPost $post, PostRevision $newPost ) {
		$this->postsImported[] = $newPost;
	}
}
