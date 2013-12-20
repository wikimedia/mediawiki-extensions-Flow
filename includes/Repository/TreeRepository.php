<?php

namespace Flow\Repository;

use Flow\Data\ObjectManager;
use Flow\DbFactory;
use Flow\Model\UUID;
use BagOStuff;
use Flow\Exception\DataModelException;

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
			throw new DataModelException( 'Failed inserting new tree node', 'process-data' );
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
	 * Given a list of nodes, find the path from each node to the root of its tree.
	 * the root must be the first element of the array, $node must be the last element.
	 * @param $descendants array Array of UUID objects to find the root paths for.
	 * @return array Associative array, key is the post ID in hex, value is the path as an array.
	 */
	public function findRootPaths( array $descendants ) {
		$cacheKeys = array();
		$cacheValues = array();
		$missingValues = array();

		foreach( $descendants as $descendant ) {
			$cacheKeys[$descendant->getHex()] = wfForeignMemcKey( 'flow', 'tree', 'rootpath', $descendant->getHex() );
		}

		$cacheResult = $this->cache->getMulti( array_values( $cacheKeys ) );

		foreach( $descendants as $descendant ) {
			if ( isset( $cacheResult[$cacheKeys[$descendant->getHex()]] ) ) {
				$cacheValues[$descendant->getHex()] = $cacheResult[$cacheKeys[$descendant->getHex()]];
			} else {
				// This doubles as a way to convert binary UUIDs to hex
				$missingValues[$descendant->getBinary()] = $descendant->getHex();
			}
		}

		if ( ! count( $missingValues ) ) {
			return $cacheValues;
		}

		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		$res = $dbr->select(
			$this->tableName,
			array( 'tree_descendant_id', 'tree_ancestor_id', 'tree_depth' ),
			array(
				'tree_descendant_id' => array_keys( $missingValues ),
			),
			__METHOD__
		);

		if ( !$res || $res->numRows() === 0 ) {
			return $cacheValues;
		}

		$paths = array_fill_keys( array_keys( $missingValues ), array() );
		foreach ( $res as $row ) {
			$hexId = $missingValues[$row->tree_descendant_id];
			$paths[$hexId][$row->tree_depth] = UUID::create( $row->tree_ancestor_id );
		}

		foreach( $paths as $descendantId => &$path) {
			if ( !$path ) {
				$path = null;
				continue;
			}

			ksort( $path );
			$path = array_reverse( $path );

			$this->cache->set( $cacheKeys[$descendantId], $path, $this->cacheTime );
		}

		return $paths + $cacheValues;
	}

	/**
	 * Finds the root path for a single post ID.
	 * @param  UUID   $descendant Post ID
	 * @return array Path to the root of that node.
	 */
	public function findRootPath( UUID $descendant ) {
		$paths = $this->findRootPaths( array( $descendant ) );

		return isset( $paths[$descendant->getHex()] ) ? $paths[$descendant->getHex()] : null;
	}

	/**
	 * Finds the root posts of a list of posts.
	 * @param  array  $descendants Array of PostRevision objects to find roots for.
	 * @return array Associative array of post ID (as hex) to UUID object representing its root.
	 */
	public function findRoots( array $descendants ) {
		$paths = $this->findRootPaths( $descendants );
		$roots = array();

		foreach( $descendants as $descendant ) {
			if ( isset( $paths[$descendant->getHex()] ) ) {
				$roots[$descendant->getHex()] = $paths[$descendant->getHex()][0];
			}
		}

		return $roots;
	}

	/**
	 * Given a specific child node find the associated root node
	 */
	public function findRoot( UUID $descendant ) {
		// To simplify caching we will work through the root path instead
		// of caching our own value
		$path = $this->findRootPath( $descendant );
		$root = array_shift( $path );

		if ( ! $root ) {
			throw new DataModelException( $descendant->getHex().' has no root post. Probably is a root post.', 'process-data' );
		}

		return $root;
	}

	/**
	 * Fetch a node and all its descendants.
	 * @return array Multi-dimensional tree
	 */
	public function fetchSubtreeIdentityMap( $root, $maxDepth = null ) {
		$nodes = $this->fetchSubtreeNodeList( ObjectManager::makeArray( $root ) );
		if ( !$nodes ) {
			throw new DataModelException( 'subtree node list should have at least returned root: ' . $root, 'process-data' );
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
			throw new DataModelException( 'No root exists in the identityMap', 'process-data' );
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
				throw new DataModelException( 'Already have a parent for ' . $node->tree_descendant_id, 'process-data' );
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
