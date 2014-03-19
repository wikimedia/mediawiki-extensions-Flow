<?php

namespace Flow\Data;

use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use MWException;

/**
 * Slight tweak to the TopKIndex uses additional info from TreeRepository to build the cache
 */
class TopicHistoryIndex extends TopKIndex {

	protected $treeRepository;

	public function __construct( BufferedCache $cache, PostRevisionStorage $storage, TreeRepository $treeRepo, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_root_id' ) ) {
			throw new \MWException( __CLASS__ . ' is hardcoded to only index topic_root_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->treeRepository = $treeRepo;
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $new
	 */
	public function onAfterInsert( $object, array $new ) {
		$new['topic_root_id'] = $object->getRootPost()->getPostId()->getBinary();
		parent::onAfterInsert( $object, $new );
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $old
	 * @param string[] $new
	 */
	public function onAfterUpdate( $object, array $old, array $new ) {
		$old['topic_root_id'] = $new['topic_root_id'] = $object->getRootPost()->getPostId()->getBinary();
		parent::onAfterUpdate( $object, $old, $new );
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $old
	 */
	public function onAfterRemove( $object, array $old ) {
		$old['topic_root_id'] = $object->getRootPost()->getPostId()->getBinary();
		parent::onAfterRemove( $object, $old );
	}

	protected function backingStoreFindMulti( array $queries, array $cacheKeys ) {
		// all queries are for roots( guaranteed by constructor), so anything that falls
		// through and has to be queried from storage will actually need to be doing a
		// special condition either joining against flow_tree_node or first collecting the
		// subtree node lists and then doing a big IN condition

		// This isn't a hot path(should be pre-populated into index) but we still dont want
		// horrible performance

		$roots = array();
		foreach ( $queries as $features ) {
			$roots[] = UUID::create( $features['topic_root_id'] );
		}
		$nodeList = $this->treeRepository->fetchSubtreeNodeList( $roots );
		if ( $nodeList === false ) {
			// We can't return the existing $retval, that false data would be cached.
			return false;
		}

		$descendantQueries = array();
		foreach ( $queries as $idx => $features ) {
			/** @var UUID $topicRootId */
			$topicRootId = $features['topic_root_id'];
			$nodes = $nodeList[$topicRootId->getAlphadecimal()];
			$descendantQueries[$idx] = array(
				'tree_rev_descendant_id' => UUID::convertUUIDs( $nodes ),
			);
		}

		$options = $this->queryOptions();
		$res = $this->storage->findMulti( $descendantQueries, $options );
		if  ( !$res ) {
			return false;
		}

		$results = array();

		foreach ( $res as $idx => $rows ) {
			$results[$idx] = $rows;
			$this->cache->add( $cacheKeys[$idx], $this->rowCompactor->compactRows( $rows ) );
			unset( $queries[$idx] );
		}
		if ( $queries ) {
			// Log something about not finding everything?
		}
		return $results;
	}
}
