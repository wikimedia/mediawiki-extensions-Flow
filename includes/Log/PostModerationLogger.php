<?php

namespace Flow\Log;

use Flow\Data\LifecycleHandler;
use Flow\Data\ManagerGroup;
use Flow\Log\Logger;
use Flow\Model\AbstractRevision;
use Flow\Repository\TreeRepository;

class PostModerationLogger implements LifecycleHandler {
	function __construct( ManagerGroup $storage, TreeRepository $treeRepo, Logger $logger ) {
		$this->storage = $storage;
		$this->treeRepo = $treeRepo;
		$this->logger = $logger;
	}

	function onAfterInsert( $object, array $row ) {
		$moderationChangeTypes = self::getModerationChangeTypes();
		if ( ! in_array( $object->getChangeType(), $moderationChangeTypes ) ) {
			// Do nothing for non-moderation actions
			return;
		}

		$lastRevId = $object->getPrevRevisionId();
		$lastRevision = $this->storage->get( 'PostRevision', $lastRevId );

		if ( $this->logger->canLog( $lastRevision, $object->getChangeType() ) ) {
			// This is awful but it's all I can think of
			$rootPost = $this->treeRepo->findRoot( $lastRevision->getPostId() );
			$workflow = $this->storage->get( 'Workflow', $rootPost );

			$this->logger->log(
				$lastRevision,
				$object->getChangeType(),
				$object->getModeratedReason(),
				$workflow,
				array(
					'postId' => $object->getPostId()->getHex(),
				)
			);
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

	protected static function getModerationChangeTypes() {
		static $changeTypes = false;

		if ( ! $changeTypes ) {
			$changeTypes = array();
			foreach( AbstractRevision::$perms as $perm => $info ) {
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