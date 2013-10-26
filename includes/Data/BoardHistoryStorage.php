<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\Model\PostRevision;
use Flow\Model\Header;
use Flow\DbFactory;
use Flow\Repository\TreeRepository;
use Flow\Container;

class BoardHistoryStorage implements WritableObjectStorage {

	protected $dbFactory;

	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	public function getIterator() {
		throw new \MWException( 'Not Implemented' );
	}

	function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( $attributes, $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	function findMulti( array $queries, array $options = array() ) {
		$res =  RevisionStorage::mergeExternalContent(
			 array( $this->findHeaderHistory( $queries, $options ) + $this->findTopicListHistory( $queries, $options ) )
		);

		return $res;
	}

	function findHeaderHistory( array $queries, array $options = array() ) {
		$queries = current( $queries );
		
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
		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			array( 'flow_topic_list', 'flow_tree_revision', 'flow_revision' ),
			array( '*' ),
			array( 'tree_rev_id = rev_id', 'tree_rev_descendant_id = topic_id' ) + UUID::convertUUIDs( current( $queries ) ),
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

}

class BoardHistoryIndex extends topKIndex {

	protected $treeRepository;

	public function __construct( BufferedCache $cache, BoardHistoryStorage $storage, TreeRepository $treeRepo, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new \Exception( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->treeRepository = $treeRepo;
	}

	public function isBuildIndex() {
		return false;	
	}

	public function backingStoreFindMulti( array $queries, array $idxToKey, array $retval = array() ) {
		$res = $this->storage->findMulti( $queries, $this->queryOptions() );
		if  ( !$res ) {
			return false;
		}

		$this->cache->add( current( $idxToKey ), $this->rowCompactor->compactRows( $res[0] ) );
		$retval[] = $res[0];

		return $retval;
	}
}

class BoardHistoryTopicListIndex extends TopKIndex {

	protected $treeRepository;

	public function __construct( BufferedCache $cache, BoardHistoryStorage $storage, TreeRepository $treeRepo, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new \Exception( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->treeRepository = $treeRepo;
	}

	public function isSearchIndex() {
		return false;	
	}

	public function onAfterInsert( $object, array $new ) {
		$post = UUID::create( $new['tree_rev_descendant_id'] );
		if ( $this->isRootPost( $post ) ) {
			$topicListId = $this->findTopicListId( $post );
			if ( $topicListId ) {
				$new['topic_list_id'] = $topicListId;
				parent::onAfterInsert( $object, $new );
			}
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$post = UUID::create( $old['tree_rev_descendant_id'] );
		if ( $this->isRootPost( $post ) ) {
			$topicListId = $this->findTopicListId( $post );
			if ( $topicListId ) {
				$new['topic_list_id'] = $old['topic_list_id'] = $topicListId;
				parent::onAfterUpdate( $object, $old, $new );
			}
		}
	}

	public function onAfterRemove( $object, array $old ) {
		$post = UUID::create( $old['tree_rev_descendant_id'] );
		if ( $this->isRootPost( $post ) ) {
			$topicListId = $this->findTopicListId( $post );
			if ( $topicListId ) {
				$old['topic_list_id'] = $topicListId;
				parent::onAfterRemove( $object, $old );
			}
		}
	}

	protected function isRootPost( $postId ) {
		$parent = $this->treeRepository->findParent( $postId );
		if ( $parent ) {
			return false;
		} else {
			return true;
		}
	}

	protected function findTopicListId( $post ) {
		$topicListEntry = Container::get( 'storage' )->find(
			'TopicListEntry',
			array( 'topic_id' => $post->getBinary() )
		);

		if ( $topicListEntry ) {
			$topicListEntry = current( $topicListEntry );
			return $topicListEntry->getListId()->getBinary();
		} else {
			return false;
		}
	}

}

class BoardHistoryHeaderIndex extends TopKIndex {

	protected $treeRepository;

	public function __construct( BufferedCache $cache, BoardHistoryStorage $storage, TreeRepository $treeRepo, $prefix, array $indexed, array $options = array() ) {
		if ( $indexed !== array( 'topic_list_id' ) ) {
			throw new \Exception( __CLASS__ . ' is hardcoded to only index topic_list_id: ' . print_r( $indexed, true ) );
		}
		parent::__construct( $cache, $storage, $prefix, $indexed, $options );
		$this->treeRepository = $treeRepo;
	}

	public function isSearchIndex() {
		return false;
	}
	
	public function onAfterInsert( $object, array $new ) {
		$new['topic_list_id'] = $new['header_workflow_id'];
		parent::onAfterInsert( $object, $new );
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$new['topic_list_id'] = $old['topic_list_id'] = $new['header_workflow_id'];
		parent::onAfterUpdate( $object, $old, $new );
	}

	public function onAfterRemove( $object, array $old ) {
		$old['topic_list_id'] = $old['header_workflow_id'];
		parent::onAfterRemove( $object, $old );
	}

}
