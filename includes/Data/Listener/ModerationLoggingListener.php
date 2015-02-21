<?php

namespace Flow\Data\Listener;

use Flow\Data\LifecycleHandler;
use Flow\Log\ModerationLogger;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;

class ModerationLoggingListener implements LifecycleHandler {

	/**
	 * @var ModerationLogger
	 */
	protected $moderationLogger;

	function __construct( ModerationLogger $moderationLogger ) {
		$this->moderationLogger = $moderationLogger;
	}

	/**
	 * @param PostRevision $object
	 * @param array $row
	 * @param array $metadata
	 */
	function onAfterInsert( $object, array $row, array $metadata ) {
		if ( $object instanceof PostRevision ) {
			$this->log( $object );
		}
	}

	function onAfterLoad( $object, array $old ) {
		 // You don't need to see my identification
	}

	function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// These aren't the droids you're looking for
	}

	function onAfterRemove( $object, array $old, array $metadata ) {
		// Move along
	}

	protected function log( PostRevision $post ) {
		$moderationChangeTypes = self::getModerationChangeTypes();
		if ( ! in_array( $post->getChangeType(), $moderationChangeTypes ) ) {
			// Do nothing for non-moderation actions
			return;
		}

		if ( $this->moderationLogger->canLog( $post, $post->getChangeType() ) ) {
			$workflowId = $post->getRootPost()->getPostId();
			$logParams = array();

			if ( $post->isTopicTitle() ) {
				$logParams['topicId'] = $workflowId;
			} else {
				$logParams['postId'] = $post->getRevisionId();
			}

			$this->moderationLogger->log(
				$post,
				$post->getChangeType(),
				$post->getModeratedReason(),
				$workflowId,
				$logParams
			);
		}
	}

	public static function getModerationChangeTypes() {
		static $changeTypes = false;

		if ( ! $changeTypes ) {
			$changeTypes = array();
			foreach( AbstractRevision::$perms as $perm ) {
				if ( $perm != '' ) {
					$changeTypes[] = "{$perm}-topic";
					$changeTypes[] = "{$perm}-post";
				}
			}

			$changeTypes[] = 'restore-topic';
			$changeTypes[] = 'restore-post';
		}

		return $changeTypes;
	}
}
