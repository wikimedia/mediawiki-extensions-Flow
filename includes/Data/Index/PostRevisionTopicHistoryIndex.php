<?php

namespace Flow\Data\Index;

use Flow\Data\BufferedCache;
use Flow\Data\ObjectMapper;
use Flow\Data\Storage\PostRevisionTopicHistoryStorage;
use Flow\Exception\InvalidInputException;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\Workflow;
use Flow\Model\UUID;
use MWException;

/**
 * TopKIndex that calculates the topic_root_id
 */
class PostRevisionTopicHistoryIndex extends TopKIndex {
	public function __construct( BufferedCache $cache, PostRevisionTopicHistoryStorage $storage, ObjectMapper $mapper, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_root_id' ) ) {
			throw new \MWException( __CLASS__ . ' is hardcoded to only index topic_root_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $mapper, $prefix, $indexed, $options );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param array $row
	 */
	public function cachePurge( $object, array $row ) {
		$row['topic_root_id'] = $this->findTopicId( $object, array() );
		parent::cachePurge( $object, $row );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$new['topic_root_id'] = $this->findTopicId( $object, $metadata );
		parent::onAfterInsert( $object, $new, $metadata );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $old
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$old['topic_root_id'] = $new['topic_root_id'] = $this->findTopicId( $object, $metadata );
		parent::onAfterUpdate( $object, $old, $new, $metadata );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $old
	 * @param array $metadata
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$old['topic_root_id'] = $this->findTopicId( $object, $metadata );
		parent::onAfterRemove( $object, $old, $metadata );
	}

	/**
	 * Finds topic ID for given Post
	 *
	 * @param PostRevision $post
	 * return UUID Topic ID
	 */
	protected function findTopicId( PostRevision $post ) {
		return $post->getRootPost()->getPostId();
	}

	protected function backingStoreFindMulti( array $queries ) {
		return $this->storage->findMulti( $queries, $this->queryOptions() );
	}
}
