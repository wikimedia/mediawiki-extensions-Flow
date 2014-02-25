<?php

namespace Flow\Data;

use Flow\DbFactory;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Container;
use Flow\Exception\DataModelException;

class BoardHistoryStorage extends DbStorage {

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( $attributes, $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) > 1 ) {
			throw new DataModelException( __METHOD__ . ' expects only one value in $queries', 'process-data' );
		}
		$merged = $this->findHeaderHistory( $queries, $options ) +
			$this->findTopicListHistory( $queries, $options );
		uasort( $merged, function( $a, $b ) {
			// $b first causes newest items at the begining of the list
			return strcmp( $b['rev_id'], $a['rev_id'] );
		} );
		return RevisionStorage::mergeExternalContent( array( $merged ) );
	}

	function findHeaderHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_header_revision', 'flow_revision' ),
			array( '*' ),
			array( 'header_rev_id = rev_id' ) + UUID::convertUUIDs( array( 'header_workflow_id' => $queries['topic_list_id'] ) ),
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$retval[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
			}
		}
		return $retval;
	}

	function findTopicListHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_topic_list', 'flow_tree_node', 'flow_tree_revision', 'flow_revision' ),
			array( '*' ),
			array(
				'topic_id = tree_ancestor_id',
				'tree_descendant_id = tree_rev_descendant_id',
				'tree_rev_id = rev_id',
			) + $queries,
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$retval[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
			}
		}
		return $retval;
	}

	public function getPrimaryKeyColumns() {
		return array( 'topic_list_id' );
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

	public function getIterator() {
		throw new DataModelException( 'Not Implemented', 'process-data' );
	}

}

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
		if ( $object instanceof Header ) {
			$new['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterInsert( $object, $new );
		} elseif ( $object instanceof PostRevision ) {
			$topicListId = $this->findTopicListIdForRootPost( $object->getRootPost() );
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
			$new['topic_list_id'] = $old['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterUpdate( $object, $old, $new );
		} elseif ( $object instanceof PostRevision ) {
			$topicListId = $this->findTopicListIdForRootPost( $object->getRootPost() );
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
			$old['topic_list_id'] = $old['header_workflow_id'];
			parent::onAfterRemove( $object, $old );
		} elseif ( $object instanceof PostRevision ) {
			$topicListId = $this->findTopicListIdForRootPost( $object->getRootPost() );
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
	protected function findTopicListId( PostRevision $object ) {
		$topicListEntry = Container::get( 'storage' )->find(
			'TopicListEntry',
			array( 'topic_id' => $object->getRootPost()->getPostId() )
		);

		if ( $topicListEntry ) {
			$topicListEntry = reset( $topicListEntry );
			return $topicListEntry->getListId()->getBinary();
		} else {
			return false;
		}
	}
}
