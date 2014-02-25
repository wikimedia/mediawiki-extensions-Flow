<?php

namespace Flow\Log;

use Flow\Data\LifecycleHandler;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;

class PostModerationLogger implements LifecycleHandler {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var TreeRepository
	 */
	protected $treeRepo;

	/**
	 * @var Logger
	 */
	protected $logger;

	function __construct( ManagerGroup $storage, TreeRepository $treeRepo, Logger $logger ) {
		$this->storage = $storage;
		$this->treeRepo = $treeRepo;
		$this->logger = $logger;
	}

	/**
	 * @param PostRevision $object
	 * @param array $row
	 */
	function onAfterInsert( $object, array $row ) {
		if ( $object instanceof PostRevision ) {
			$this->log( $object );
		}
	}

	function onAfterLoad( $object, array $old ) {
		 // You don't need to see my identification
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// These aren't the droids you're looking for
	}

	function onAfterRemove( $object, array $old ) {
		// Move along
	}

	protected function log( PostRevision $post ) {
		$moderationChangeTypes = self::getModerationChangeTypes();
		if ( ! in_array( $post->getChangeType(), $moderationChangeTypes ) ) {
			// Do nothing for non-moderation actions
			return;
		}

		if ( $this->logger->canLog( $post, $post->getChangeType() ) ) {
			$rootPostId = $post->getRootPost()->getPostId();
			$workflow = $this->storage->get( 'Workflow', $rootPostId );
			if ( !$workflow ) {
				// unless in unit test, write to log
				wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate workflow " . $rootPostId->getAlphadecimal() );
				return;
			}

			$logParams = array();

			if ( $post->isTopicTitle() ) {
				$logParams['topicId'] = $workflow->getId();
			} else {
				$logParams['postId'] = $post->getRevisionId();
			}

			$this->logger->log(
				$post,
				$post->getChangeType(),
				$post->getModeratedReason(),
				$workflow,
				$logParams
			);
		}
	}

	protected static function getModerationChangeTypes() {
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
