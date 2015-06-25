<?php

namespace Flow\Data\Storage;

use Flow\Data\ObjectStorage;
use Flow\Exception\DataModelException;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;

/**
 * Query-only storage implementation merges PostRevision and
 * PostSummary instances to provide a full list of revisions for
 * a topics history.
 */
class TopicHistoryStorage implements ObjectStorage {

	/**
	 * @var ObjectStorage
	 */
	protected $postRevisionStorage;

	/**
	 * @var ObjectStorage
	 */
	protected $postSummaryStorage;

	/**
	 * @var TreeRepository
	 */
	protected $treeRepository;

	/**
	 * @param ObjectStorage $postRevisionStorage
	 * @param ObjectStorage $postSummaryStorage
	 * @param TreeRepository $treeRepo
	 */
	public function __construct( ObjectStorage $postRevisionStorage, ObjectStorage $postSummaryStorage, TreeRepository $treeRepo ) {
		$this->postRevisionStorage = $postRevisionStorage;
		$this->postSummaryStorage = $postSummaryStorage;
		$this->treeRepository = $treeRepo;
	}

	public function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( array( $attributes ), $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	/**
	 * This can be called with 2 different kinds of input:
	 * * queries for 'topic_root_id': a "virtual" column that we'll interpret.
	 *   Based on these root ids (=topic id), we'll fetch all revision (post &
	 *   summary) inside that topic.
	 * * queries for 'rev_id': by ShallowCompactor, when expanding: when caching
	 *   lists like these, we only store the id & then expand the full list from
	 *   other cache data. Shallow storage for TopicHistoryIndex is defined by
	 *   storage.topic_history.indexes.primary (it's compacted by rev_id)
	 *   rev_id can contain ids for both posts & summaries.
	 *
	 * @param array $queries
	 * @param array $options
	 * @return array
	 */
	public function findMulti( array $queries, array $options = array() ) {
		foreach ( $queries as $idx => $query ) {
			if ( isset( $query['topic_root_id'] ) ) {
				$queries[$idx] = $this->findDescendantQuery( $query );
			}
		}

		return $this->findDescendants( $queries, $options );
	}

	/**
	 * All queries are for roots (guaranteed in findMulti), so anything that falls
	 * through and has to be queried from storage will actually need to be doing a
	 * special condition either joining against flow_tree_node or first collecting the
	 * subtree node lists and then doing a big IN condition
	 *
	 * This isn't a hot path (should be pre-populated into index) but we still don't want
	 * horrible performance
	 *
	 * @param array $queries
	 * @return array
	 * @throws \Flow\Exception\InvalidInputException
	 */
	protected function findDescendantQuery( array $query ) {
		$roots = array( UUID::create( $query['topic_root_id'] ) );
		$nodeList = $this->treeRepository->fetchSubtreeNodeList( $roots );
		if ( $nodeList === false ) {
			// We can't return the existing $retval, that false data would be cached.
			return array();
		}

		/** @var UUID $topicRootId */
		$topicRootId = UUID::create( $query['topic_root_id'] );
		$nodes = $nodeList[$topicRootId->getAlphadecimal()];
		return array(
			'rev_type_id' => UUID::convertUUIDs( $nodes ),
		);
	}

	public function findDescendants( array $queries, array $options = array() ) {
		$data = $this->postRevisionStorage->findMulti( $queries, $options );
		$summary = $this->postSummaryStorage->findMulti( $queries, $options );
		if ( $summary ) {
			if ( $data ) {
				foreach ( $summary as $key => $rows ) {
					if ( isset( $data[$key] ) ) {
						$data[$key] += $rows;
						// Failing to sort is okay, we'd rather display unordered
						// result than showing an error page with exception
						krsort( $data[$key] );
					} else {
						$data[$key] = $rows;
					}
				}
			} else {
				$data = $summary;
			}
		}
		return $data;
	}

	public function getPrimaryKeyColumns() {
		return array( 'topic_root_id' );
	}

	public function insert( array $row ) {
		throw new DataModelException( __CLASS__ . ' does not support insert action', 'process-data' );
	}

	public function update( array $old, array $new ) {
		throw new DataModelException( __CLASS__ . ' does not support update action', 'process-data' );
	}

	public function remove( array $row ) {
		throw new DataModelException( __CLASS__ . ' does not support remove action', 'process-data' );
	}

	public function validate( array $row ) {
		return true;
	}
}
