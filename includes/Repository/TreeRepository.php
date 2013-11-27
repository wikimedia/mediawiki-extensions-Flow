<?php

namespace Flow\Repository;

use Flow\Data\ObjectManager;
use Flow\DbFactory;
use Flow\Model\UUID;
use BagOStuff;
use MWException;

/*
 *
 * In SQL
 *
 * CREATE TABLE flow_tree_node (
 *     descendant DECIMAL(39) UNSIGNED NOT NULL,
 *     ancestor DECIMAL(39) UNSIGNED NULL,
 *     depth SMALLINT UNSIGNED NOT NULL,
 *     PRIMARY KEY ( ancestor, descendant ),
 *     UNIQUE KEY ( descendant, depth )
 * );
 *
 * In Memcache
 *
 * flow:tree:subtree:<descendant>
 * flow:tree:rootpath:<descendant>
 * flow:tree:parent:<descendant> - should we just use rootpath?
 *
 * Not sure how to handle topic splits with caching yet, i can imagine
 * a number of potential race conditions for writing root paths and sub trees
 * during a topic split
*/
class TreeRepository {
	protected $tableName = 'flow_tree_node';

	/**
	 * @param DbFactory $dbFactory Factory to source connection objects from
	 * @param BagOStuff $cache
	 * @param integer $cacheTime How long to cache data in memcache
	 */
	public function __construct( DbFactory $dbFactory, BagOStuff $cache, $cacheTime = 0 ) {
		$this->dbFactory = $dbFactory;
		$this->cache = $cache;
		$this->cacheTime = $cacheTime;
	}

	/**
	 * Insert a new tree node.  If ancestor === null then this node is a root.
	 *
	 * To also write this to cache we would have to read our own write, which
	 * isn't guaranteed during a node split. Master reads can potentially be
	 * a different server than master writes.
	 *
	 * The way to do it without that is to CAS update memcache, assuming it currently
	 * has what we need
	 */
	public function insert( UUID $descendant, UUID $ancestor = null ) {
		$subtreeKey = wfForeignMemcKey( 'flow', '', 'tree', 'subtree', $descendant->getHex() );
		$parentKey = wfForeignMemcKey( 'flow', '', 'tree', 'parent', $descendant->getHex() );
		$pathKey = wfForeignMemcKey( 'flow', '', 'tree', 'rootpath', $descendant->getHex() );
		$this->cache->set( $subtreeKey, array( $descendant ), $this->cacheTime );
		if ( $ancestor === null ) {
			$this->cache->set( $parentKey, null, $this->cacheTime );
			$this->cache->set( $pathKey, array( $descendant ), $this->cacheTime );
			$path = array( $descendant );
		} else {
			$this->cache->set( $parentKey, $ancestor, $this->cacheTime );
			$path = $this->findRootPath( $ancestor );
			$path[] = $descendant;
			$this->cache->set( $pathKey, $path, $this->cacheTime );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			$this->tableName,
			array(
				'tree_descendant_id' => $descendant->getBinary(),
				'tree_ancestor_id' => $descendant->getBinary(),
				'tree_depth' => 0,
			),
			__METHOD__
		);
		if ( $res && $ancestor !== null ) {
			$res = $dbw->insertSelect(
				$this->tableName,
				$this->tableName,
				array(
					'tree_descendant_id' => $dbw->addQuotes( $descendant->getBinary() ),
					'tree_ancestor_id' => 'tree_ancestor_id',
					'tree_depth' => 'tree_depth + 1',
				),
				array(
					'tree_descendant_id' => $ancestor->getBinary(),
				),
				__METHOD__
			);
		}
		if ( !$res ) {
			$this->cache->del( $parentKey );
			$this->cache->del( $pathKey );
			throw new MWEception( 'Failed inserting new tree node' );
		}
		$this->appendToSubtreeCache( $descendant, $path );
		return true;
	}

	protected function appendToSubtreeCache( UUID $descendant, array $rootPath ) {
		$callback = function( BagOStuff $cache, $key, $value ) use( $descendant ) {
			if ( $value === false ) {
				return false;
			}
			$value[$descendant->getHex()] = $descendant;
			return $value;
		};
		// This could be pretty slow if there is contention
		foreach ( $rootPath as $subtreeRoot ) {
			$this->cache->merge(
				wfForeignMemcKey( 'flow', '', 'tree', 'subtree', $subtreeRoot->getHex() ),
				$callback,
				$this->cacheTime
			);
		}
	}
	public function findParent( UUID $descendant ) {
		$map = $this->fetchParentMap( array( $descendant ) );
		return isset( $map[$descendant->getHex()] ) ? $map[$descendant->getHex()] : null;
	}

	/**
	 * Given a specific child node find the path from that node to the root of its tree.
	 * the root must be the first element of the array, $node must be the last element.
	 */
	public function findRootPath( UUID $descendant ) {
		$cacheKey = wfForeignMemcKey( 'flow', '', 'tree', 'rootpath', $descendant->getHex() );
		$path = $this->cache->get( $cacheKey );
		if ( $path !== false ) {
			return $path;
		}

		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		$res = $dbr->select(
			$this->tableName,
			array( 'tree_ancestor_id', 'tree_depth' ),
			array(
				'tree_descendant_id' => $descendant->getBinary(),
			),
			__METHOD__
		);
		if ( !$res ) {
			return null;
		}
		$path = array();
		foreach ( $res as $row ) {
			$path[$row->tree_depth] = UUID::create( $row->tree_ancestor_id );
		}
		if ( !$path ) {
			throw new \MWException( 'No root path found? Is this a root already? ' . $descendant->getHex() );
		}
		ksort( $path );
		$path = array_reverse( $path );
		$this->cache->set( $cacheKey, $path, $this->cacheTime );
		return $path;
	}

	/**
	 * Given a specific child node find the associated root node
	 */
	public function findRoot( UUID $descendant ) {
		// To simplify caching we will work through the root path instead
		// of caching our own value
		$path = $this->findRootPath( $descendant );
		return array_shift( $path );
	}

	/**
	 * Fetch a node and all its descendants.
	 * @return array Multi-dimensional tree
	 */
	public function fetchSubtreeIdentityMap( $root, $maxDepth = null ) {
		$nodes = $this->fetchSubtreeNodeList( ObjectManager::makeArray( $root ) );
		if ( !$nodes ) {
			throw new \MWException( 'subtree node list should have at least returned root: ' . $root );
		} elseif ( count( $nodes ) === 1 ) {
			$parentMap = $this->fetchParentMap( reset( $nodes ) );
		} else {
			$parentMap = $this->fetchParentMap( call_user_func_array( 'array_merge', $nodes ) );
		}
		$identityMap = array();
		foreach ( $parentMap as $child => $parent ) {
			if ( !isset( $identityMap[$child] ) ) {
				$identityMap[$child] = array( 'children' => array() );
			}
			// Root nodes have no parent
			if ( $parent !== null ) {
				$identityMap[$parent]['children'][] =& $identityMap[$child];
			}
		}
		return $identityMap;
	}

	public function fetchSubtree( UUID $root, $maxDepth = null ) {
		$identityMap = $this->fetchSubtreeIdentityMap( $root, $maxDepth );
		if ( !isset( $identityMap[$root->getHex()] ) ) {
			throw new MWException( 'No root exists in the identityMap' );
		}

		return $identityMap[$root];
	}

	public function fetchFullTree( UUID $nodeId ) {
		return $this->fetchSubtree( $this->findRoot( $nodeId ) );
	}

	/**
	 * Return the id's of all nodes which are a descendant of provided roots
	 * @return array map from root id to its descendant list
	 */
	public function fetchSubtreeNodeList( array $roots ) {
		$list = new MultiGetList( $this->cache, $this->cacheTime );
		$res = $list->get(
			array( 'tree', 'subtree' ),
			$roots,
			array( $this, 'fetchSubtreeNodeListFromDb' )
		);
		if ( $res === false ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Failure fetching node list from cache' );
			return false;
		}
		// $idx is a binary UUID
		$retval = array();
		foreach ( $res as $idx => $val ) {
			$retval[UUID::create( $idx )->getHex()] = $val;
		}
		return $retval;
	}

	public function fetchSubtreeNodeListFromDb( array $roots ) {
		$res = $this->dbFactory->getDB( DB_SLAVE )->select(
			$this->tableName,
			array( 'tree_ancestor_id', 'tree_descendant_id' ),
			array(
				'tree_ancestor_id' => UUID::convertUUIDs( $roots ),
			),
			__METHOD__
		);
		if ( $res === false ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Failure fetching node list from database' );
			return false;
		}
		if ( !$res ) {
			return array();
		}
		$nodes = array();
		foreach ( $res as $node ) {
			$ancestor = UUID::create( $node->tree_ancestor_id );
			$descendant = UUID::create( $node->tree_descendant_id );
			$nodes[$ancestor->getHex()][$descendant->getHex()] = $descendant;
		}

		return $nodes;
	}

	/**
	 * Fetch the id of the immediate parent node of all ids in $nodes.  Non-existant
	 * nodes are not represented in the result set.
	 */
	protected function fetchParentMap( array $nodes ) {
		$list = new MultiGetList( $this->cache, $this->cacheTime );
		return $list->get(
			array( 'tree', 'parent' ),
			$nodes,
			array( $this, 'fetchParentMapFromDb' )
		);
	}

	public function fetchParentMapFromDb( array $nodes ) {
		// Find out who the parent is for those nodes
		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		$res = $dbr->select(
			$this->tableName,
			array( 'tree_ancestor_id', 'tree_descendant_id' ),
			array(
				'tree_descendant_id' => UUID::convertUUIDs( $nodes ),
				'tree_depth' => 1,
			),
			__METHOD__
		);
		if ( !$res ) {
			return array();
		}
		$result = array();
		foreach ( $res as $node ) {
			if ( isset( $result[$node->tree_descendant_id] ) ) {
				throw new MWException( 'Already have a parent for ' . $node->tree_descendant_id );
			}
			$descendant = UUID::create( $node->tree_descendant_id );
			$result[$descendant->getHex()] = UUID::create( $node->tree_ancestor_id );
		}
		foreach ( $nodes as $node ) {
			if ( !isset( $result[$node] ) ) {
				// $node is a root, it has no parent
				$result[$node] = null;
			}
		}

		return $result;
	}
}
