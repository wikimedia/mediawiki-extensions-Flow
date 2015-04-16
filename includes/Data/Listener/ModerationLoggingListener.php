<?php

namespace Flow\Data\Listener;

use Flow\Log\ModerationLogger;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;

class ModerationLoggingListener extends AbstractListener {

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
	 * @param array $metadata (must contain 'workflow' key with a Workflow object)
	 */
	function onAfterInsert( $object, array $row, array $metadata ) {
		if ( $object instanceof PostRevision ) {
			$this->log( $object, $metadata['workflow'] );
		}
	}

	protected function log( PostRevision $post, Workflow $workflow ) {
		$moderationChangeTypes = self::getModerationChangeTypes();
		if ( ! in_array( $post->getChangeType(), $moderationChangeTypes ) ) {
			// Do nothing for non-moderation actions
			return;
		}

		if ( $this->moderationLogger->canLog( $post, $post->getChangeType() ) ) {
			$workflowId = $workflow->getId();

			$this->moderationLogger->log(
				$post,
				$post->getChangeType(),
				$post->getModeratedReason(),
				$workflowId
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
