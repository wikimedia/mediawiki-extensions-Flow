<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\DbFactory;
use Flow\Container;

class BoardHistoryStorage extends DbStorage {

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
			throw new \MWException( __METHOD__ . ' expects only one value in $queries' );
		}
		return RevisionStorage::mergeExternalContent(
			 array(
			 	 $this->findHeaderHistory( $queries, $options ) +
			 	 $this->findTopicListHistory( $queries, $options )
			 )
		);
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
				$retval[UUID::create( $row->rev_id )->getHex()] = (array) $row;
			}
		}
		return $retval;
	}

	function findTopicListHistory( array $queries, array $options = array() ) {
		$queries = $this->preprocessSqlArray( reset( $queries ) );

		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_topic_list', 'flow_tree_revision', 'flow_revision' ),
			array( '*' ),
			array( 'tree_rev_id = rev_id', 'tree_rev_descendant_id = topic_id' ) + $queries,
			__METHOD__,
			$options
		);

		$retval = array();

		if ( $res ) {
			foreach ( $res as $row ) {
				$retval[UUID::create( $row->rev_id )->getHex()] = (array) $row;
			}
		}
		return $retval;
	}

	public function getPrimaryKeyColumns() {
		return array( 'topic_list_id' );
	}

	public function insert( array $row ) {
		throw new \MWException( __CLASS__ . ' does not support insert action' );
	}

	public function update( array $old, array $new ) {
		throw new \MWException( __CLASS__ . ' does not support update action' );
	}

	public function remove( array $row ) {
		throw new \MWException( __CLASS__ . ' does not support remove action' );
	}

	public function getIterator() {
		throw new \MWException( 'Not Implemented' );
	}

}

class BoardHistoryIndex extends TopKIndex {

	public function __construct( BufferedCache $cache, BoardHistoryStorage $storage, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new \MWException( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
	}

	public function findMulti( array $queries ) {
		if ( count( $queries ) > 1 ) {
			throw new \MWException( __METHOD__ . ' expects only one value in $queries' );
		}
		return parent::findMulti( $queries );
	}

	public function backingStoreFindMulti( array $queries, array $idxToKey, array $retval = array() ) {
		$res = $this->storage->findMulti( $queries, $this->queryOptions() );
		if  ( !$res ) {
			return false;
		}

		$res = reset( $res );

		$this->cache->add( current( $idxToKey ), $this->rowCompactor->compactRows( $res ) );
		$retval[] = $res;

		return $retval;
	}

	public function onAfterInsert( $object, array $new ) {
		if ( $new['rev_type'] === 'header' ) {
			$new['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterInsert( $object, $new );
		} elseif ( $new['rev_type'] === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$new['topic_list_id'] = $topicListId;
				parent::onAfterInsert( $object, $new );
			}
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		if ( $new['rev_type'] === 'header' ) {
			$new['topic_list_id'] = $old['topic_list_id'] = $new['header_workflow_id'];
			parent::onAfterUpdate( $object, $old, $new );
		} elseif ( $new['rev_type'] === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$new['topic_list_id'] = $old['topic_list_id'] = $topicListId;
				parent::onAfterUpdate( $object, $old, $new );
			}
		}
	}

	public function onAfterRemove( $object, array $old ) {
		if ( $new['rev_type'] === 'header' ) {
			$old['topic_list_id'] = $old['header_workflow_id'];
			parent::onAfterRemove( $object, $old );
		} elseif ( $new['rev_type'] === 'post' ) {
			$topicListId = $this->findTopicListIdForRootPost( $object );
			if ( $topicListId ) {
				$old['topic_list_id'] = $topicListId;
				parent::onAfterRemove( $object, $old );
			}
		}
	}

	/**
	 * Find a topic list id for a root post
	 */
	protected function findTopicListIdForRootPost( $object ) {
		if ( !$object->isTopicTitle() ) {
			return false;
		}

		$topicListEntry = Container::get( 'storage' )->find(
			'TopicListEntry',
			array( 'topic_id' => $object->getPostId()->getBinary() )
		);

		if ( $topicListEntry ) {
			$topicListEntry = reset( $topicListEntry );
			return $topicListEntry->getListId()->getBinary();
		} else {
			return false;
		}
	}
}
