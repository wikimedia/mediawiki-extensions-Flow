<?php

namespace Flow\Data\Index;

use Flow\Data\BufferedCache;
use Flow\Data\ObjectManager;
use Flow\Data\Storage\BoardHistoryStorage;
use Flow\Exception\DataModelException;
use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostSummary;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;

/**
 * Keeps a list of revision ids relevant to the board history bucketed
 * by the owning TopicList id (board workflow).
 *
 * Can be used with Header, PostRevision and PostSummary ObjectMapper's
 */
class BoardHistoryIndex extends TopKIndex {

	/**
	 * @var ObjectManager Manager for the TopicListEntry model
	 */
	protected $om;

	public function __construct(
		BufferedCache $cache,
		BoardHistoryStorage $storage,
		$prefix,
		array $indexed,
		array $options = array(),
		ObjectManager $om
	) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new DataModelException( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ), 'process-data' );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->om = $om;
	}

	public function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) > 1 ) {
			// why?
			throw new DataModelException( __METHOD__ . ' expects only one value in $queries', 'process-data' );
		}
		return parent::findMulti( $queries, $options );
	}

	/**
	 * @param array $queries
	 * @return array
	 */
	public function backingStoreFindMulti( array $queries ) {
		return $this->storage->findMulti(
			$queries,
			$this->queryOptions()
		) ?: array();
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $row
	 */
	public function cachePurge( $object, array $row ) {
		$row['topic_list_id'] = $this->findTopicListId( $object, $row, array() );
		parent::cachePurge( $object, $row );
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$new['topic_list_id'] = $this->findTopicListId( $object, $new, $metadata );
		parent::onAfterInsert( $object, $new, $metadata );
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$new['topic_list_id'] = $old['topic_list_id'] = $this->findTopicListId( $object, $new, $metadata );
		parent::onAfterUpdate( $object, $old, $new, $metadata );
	}

	/**
	 * @param Header|PostRevision $object
	 * @param string[] $old
	 * @param array $metadata
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$old['topic_list_id'] = $this->findTopicListId( $object, $old, $metadata );
		parent::onAfterRemove( $object, $old, $metadata );
	}

	/**
	 * Find a topic list id related to an abstract revision
	 *
	 * @param AbstractRevision $object
	 * @param string[] $row
	 * @param array $metadata
	 * @return string Alphadecimal uid of the related board
	 * @throws InvalidInputException When $object is not a Header, PostRevision or
	 *  PostSummary instance.
	 * @throws DataModelException When the related id cannot be located
	 */
	protected function findTopicListId( AbstractRevision $object, array $row, array $metadata ) {
		if ( $object instanceof Header ) {
			return $row['rev_type_id'];
		}

		if ( isset( $metadata['workflow'] ) && $metadata['workflow'] instanceof Workflow ) {
			$topicId = $metadata['workflow']->getId();
		} else {
			if ( $object instanceof PostRevision ) {
				$post = $object;
			} elseif ( $object instanceof PostSummary ) {
				$post = $object->getCollection()->getPost()->getLastRevision();
			} else {
				throw new InvalidInputException( 'Unexpected object type: ' . get_class( $object ) );
			}
			$topicId = $post->getRootPost()->getPostId();
		}

		$found = $this->om->find( array( 'topic_id' => $topicId ) );
		if ( !$found ) {
			throw new DataModelException(
				"No topic list contains topic " . $topicId->getAlphadecimal() .
				", called for revision " .  $object->getRevisionId()->getAlphadecimal()
			);
		}

		/** @var TopicListEntry $topicListEntry */
		$topicListEntry = reset( $found );
		return $topicListEntry->getListId()->getAlphadecimal();
	}
}
