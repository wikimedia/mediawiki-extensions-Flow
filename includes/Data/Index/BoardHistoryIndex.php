<?php

namespace Flow\Data\Index;

use Flow\Container;
use Flow\Data\BufferedCache;
use Flow\Data\Storage\BoardHistoryStorage;
use Flow\Exception\DataModelException;
use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostSummary;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;

/**
 * Keeps a list of revision ids relevant to the board history bucketed
 * by the owning TopicList id (board workflow)
 */
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
	 * {@inheritDoc}
	 */
	public function cachePurge( $object, array $row ) {
		$row['topic_list_id'] = $this->findTopicListId( $object, $new );
		if ( $row['topic_list_id'] ) {
			parent::cachePurge( $object, $row );
		}
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$new['topic_list_id'] = $this->findTopicListId( $object, $new );
		if ( $new['topic_list_id'] ) {
			parent::onAfterInsert( $object, $new, $metadata );
		}
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$new['topic_list_id'] = $old['topic_list_id'] = $this->findTopicListId( $object, $new );
		if ( $new['topic_list_id'] ) {
			parent::onAfterUpdate( $object, $old, $new, $metadata );
		}
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 * @param array $metadata
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$old['topic_list_id'] = $this->findTopicListId( $object, $old );
		if ( $old['topic_list_id'] ) {
			parent::onAfterRemove( $old );
		}
	}

	/**
	 * Find a topic list id related to an abstract revision
	 *
	 * @param AbstractRevision $object
	 * @return string|boolean False when object is not root post or topic is not found
	 */
	protected function findTopicListId( AbstractRevision $object, array $row ) {
		if ( $object instanceof Header ) {
			return $row['rev_type_id'];
		}

		if ( $object instanceof PostRevision ) {
			$post = $object;
		} elseif ( $object instanceof PostSummary ) {
			$post = $object->getCollection()->getPost()->getLastRevision();
		} else {
			throw new InvalidInputException( 'Unexpected object type: ' . get_class( $object ) );
		}

		$found = Container::get( 'storage' )->find(
			'TopicListEntry',
			array( 'topic_id' => $post->getRootPost()->getPostId() )
		);

		if ( $found ) {
			/** @var TopicListEntry $topicListEntry */
			$topicListEntry = reset( $found );
			return $topicListEntry->getListId()->getAlphadecimal();
		} else {
			return false;
		}
	}
}
