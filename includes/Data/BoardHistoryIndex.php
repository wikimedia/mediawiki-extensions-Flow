<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\Header;
use Flow\Model\PostSummary;
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

	public function backingStoreFindMulti( array $queries ) {
		$options = $this->queryOptions();
		$res = $this->storage->findMulti( $queries, $options );
		if  ( !$res ) {
			return array();
		}

		return $res;
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $new
	 */
	public function onAfterInsert( $object, array $new ) {
		if ( $object instanceof Header ) {
			$new['topic_list_id'] = $new['rev_type_id'];
			parent::onAfterInsert( $object, $new );
		} elseif ( $object instanceof PostRevision || $object instanceof PostSummary ) {
			if ( $object instanceof PostRevision ) {
				$postRevision = $object;
			} else {
				$postRevision = $object->getCollection()->getPost()->getLastRevision();
			}
			$topicListId = $this->findTopicListId( $postRevision );
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
		if ( $object instanceof Header ) {
			$new['topic_list_id'] = $old['topic_list_id'] = $new['rev_type_id'];
			parent::onAfterUpdate( $object, $old, $new );
		} elseif ( $object instanceof PostRevision || $object instanceof PostSummary ) {
			if ( $object instanceof PostRevision ) {
				$postRevision = $object;
			} else {
				$postRevision = $object->getCollection()->getPost()->getLastRevision();
			}
			$topicListId = $this->findTopicListId( $postRevision );
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
		if ( $object instanceof Header ) {
			$old['topic_list_id'] = $old['rev_type_id'];
			parent::onAfterRemove( $object, $old );
		} elseif ( $object instanceof PostRevision || $object instanceof PostSummary ) {
			$topicListId = $this->findTopicListId( $object );
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
	protected function findTopicListId( $object ) {
		$found = Container::get( 'storage' )->find(
			'TopicListEntry',
			array( 'topic_id' => $object->getRootPost()->getPostId() )
		);

		if ( $found ) {
			/** @var TopicListEntry $var */
			$topicListEntry = reset( $found );
			return $topicListEntry->getListId()->getAlphadecimal();
		} else {
			return false;
		}
	}
}
