<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Exception\DataModelException;

class BoardHistoryIndex extends TopKIndex {

	public function __construct( BufferedCache $cache, BoardHistoryStorage $storage, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new DataModelException( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ), 'process-data' );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
	}

	public function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) > 1 ) {
			throw new DataModelException( __METHOD__ . ' expects only one value in $queries', 'process-data' );
		}
		return parent::findMulti( $queries, $options );
	}

	public function backingStoreFindMulti( array $queries, array $cacheKeys ) {
		$options = $this->queryOptions();
		$res = $this->storage->findMulti( $queries, $options );
		if  ( !$res ) {
			return false;
		}

		$res = reset( $res );
		$this->cache->add( current( $cacheKeys ), $this->rowCompactor->compactRows( $res ) );
		return array( $res );
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $new
	 */
	public function onAfterInsert( $object, array $new ) {
		if ( $object->getRevisionType() === 'header' ) {
			$new['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterInsert( $object, $new );
		} elseif ( $object->getRevisionType() === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$new['topic_list_id'] = $topicListId;
				parent::onAfterInsert( $object, $new );
			}
		}
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 * @param string[] $new
	 */
	public function onAfterUpdate( $object, array $old, array $new ) {
		if ( $object->getRevisionType() === 'header' ) {
			$new['topic_list_id'] = $old['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterUpdate( $object, $old, $new );
		} elseif ( $object->getRevisionType() === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$new['topic_list_id'] = $old['topic_list_id'] = $topicListId;
				parent::onAfterUpdate( $object, $old, $new );
			}
		}
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 */
	public function onAfterRemove( $object, array $old ) {
		if ( $object->getRevisionType() === 'header' ) {
			$old['topic_list_id'] = $old['header_workflow_id'];
			parent::onAfterRemove( $object, $old );
		} elseif ( $object->getRevisionType() === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$old['topic_list_id'] = $topicListId;
				parent::onAfterRemove( $object, $old );
			}
		}
	}

	/**
	 * Find a topic list id for a root post
	 *
	 * @param PostRevision $object
	 * @return string|boolean False when object is not root post or topic is not found
	 */
	protected function findTopicListIdForRootPost( $object ) {
		if ( !$object->isTopicTitle() ) {
			return false;
		}

		/** @var ManagerGroup $storage */
		$storage = Container::get( 'storage' );
		/** @var TopicListEntry[] $found */
		$found = $storage->find(
			'TopicListEntry',
			array( 'topic_id' => $object->getPostId() )
		);

		if ( $found ) {
			$topicListEntry = reset( $found );
			return $topicListEntry->getListId()->getBinary();
		} else {
			return false;
		}
	}
}
