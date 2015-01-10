<?php

namespace Flow\Data\Index;

use Flow\Data\BufferedCache;
use Flow\Data\Storage\TopicHistoryStorage;
use Flow\Exception\InvalidInputException;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\Workflow;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Exception;

/**
 * Slight tweak to the TopKIndex uses additional info from TreeRepository to build the cache
 */
class TopicHistoryIndex extends TopKIndex {

	protected $treeRepository;

	public function __construct( BufferedCache $cache, TopicHistoryStorage $storage, TreeRepository $treeRepo, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_root_id' ) ) {
			throw new \Exception( __CLASS__ . ' is hardcoded to only index topic_root_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->treeRepository = $treeRepo;
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param array $row
	 */
	public function cachePurge( $object, array $row ) {
		$row['topic_root_id'] = $this->findTopicRootId( $object, array() );
		parent::cachePurge( $object, $row );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$new['topic_root_id'] = $this->findTopicRootId( $object, $metadata );
		parent::onAfterInsert( $object, $new, $metadata );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $old
	 * @param string[] $new
	 * @param array $metadata
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$old['topic_root_id'] = $new['topic_root_id'] = $this->findTopicRootId( $object, $metadata );
		parent::onAfterUpdate( $object, $old, $new, $metadata );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param string[] $old
	 * @param array $metadata
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$old['topic_root_id'] = $this->findTopicRootId( $object, $metadata );
		parent::onAfterRemove( $object, $old, $metadata );
	}

	/**
	 * @param PostRevision|PostSummary $object
	 * @param array $metadata
	 * @return string alphadecimal uuid
	 * @throws InvalidInputException When $object is not PostRevision or PostSummary
	 */
	protected function findTopicRootId( $object, array $metadata ) {
		if ( isset( $metadata['workflow'] ) && $metadata['workflow'] instanceof Workflow ) {
			return $metadata['workflow']->getId();
		} elseif ( $object instanceof PostRevision ) {
			return $object->getRootPost()->getPostId()->getAlphadecimal();
		} elseif ( $object instanceof PostSummary ) {
			return $object->getCollection()->getWorkflowId()->getAlphadecimal();
		} else {
			throw new InvalidInputException( 'Unexpected revision type: ' . get_class( $object ) );
		}
	}

	protected function backingStoreFindMulti( array $queries ) {
		// all queries are for roots( guaranteed by constructor), so anything that falls
		// through and has to be queried from storage will actually need to be doing a
		// special condition either joining against flow_tree_node or first collecting the
		// subtree node lists and then doing a big IN condition

		// This isn't a hot path (should be pre-populated into index) but we still don't want
		// horrible performance

		$roots = array();
		foreach ( $queries as $features ) {
			$roots[] = UUID::create( $features['topic_root_id'] );
		}
		$nodeList = $this->treeRepository->fetchSubtreeNodeList( $roots );
		if ( $nodeList === false ) {
			// We can't return the existing $retval, that false data would be cached.
			return array();
		}

		$descendantQueries = array();
		foreach ( $queries as $idx => $features ) {
			/** @var UUID $topicRootId */
			$topicRootId = UUID::create( $features['topic_root_id'] );
			$nodes = $nodeList[$topicRootId->getAlphadecimal()];
			$descendantQueries[$idx] = array(
				'rev_type_id' => UUID::convertUUIDs( $nodes ),
			);
		}

		$options = $this->queryOptions();
		$res = $this->storage->findMulti( $descendantQueries, $options );
		if  ( !$res ) {
			return array();
		}

		$results = array();

		foreach ( $res as $idx => $rows ) {
			$results[$idx] = $rows;
			unset( $queries[$idx] );
		}
		if ( $queries ) {
			// Log something about not finding everything?
		}
		return $results;
	}
}
