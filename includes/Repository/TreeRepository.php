<?php

namespace Flow\Repository;

use Flow\Data\BufferedCache;
use Flow\Data\ObjectManager;
use Flow\DbFactory;
use Flow\Model\UUID;
use BagOStuff;
use Flow\Container;
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

	/**
	 * @var string
	 */
	protected $tableName = 'flow_tree_node';

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var BufferedCache
	 */
	protected $cache;

	/**
	 * @param DbFactory $dbFactory Factory to source connection objects from
	 * @param BufferedCache $cache
	 */
	public function __construct( DbFactory $dbFactory, BufferedCache $cache ) {
		$this->dbFactory = $dbFactory;
		$this->cache = $cache;
	}

	/**
	 * A helper function to generate cache keys for tree repository
	 * @param string $type
	 * @param \Flow\Model\UUID $uuid
	 * @return string
	 */
	protected function cacheKey( $type, UUID $uuid ) {
		return wfForeignMemcKey( 'flow', '', 'tree', $type, $uuid->getAlphadecimal(), Container::get( 'cache.version' ) );
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
		$subtreeKey = $this->cacheKey( 'subtree', $descendant );
		$parentKey = $this->cacheKey( 'parent', $descendant );
		$pathKey = $this->cacheKey( 'rootpath', $descendant );
		$this->cache->set( $subtreeKey, array( $descendant ) );
		if ( $ancestor === null ) {
			$this->cache->set( $parentKey, null );
			$this->cache->set( $pathKey, array( $descendant ) );
			$path = array( $descendant );
		} else {
			$this->cache->set( $parentKey, $ancestor );
			$path = $this->findRootPath( $ancestor );
			$path[] = $descendant;
			$this->cache->set( $pathKey, $path );
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
			try {
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
			} catch( \DBQueryError $e ) {
				$res = false;
			}
			/*
			 * insertSelect won't work on temporary tables (as used for MW
			 * unit tests), because it refers to the same table twice, in
			 * one query.
			 * In this case, we'll do a separate select & insert. This used
			 * to always be detected via the DBQueryError, but it can also
			 * return false from insertSelect.
			 *
			 * @see https://dev.mysql.com/doc/refman/5.0/en/temporary-table-problems.html
			 * @see http://dba.stackexchange.com/questions/45270/mysql-error-1137-hy000-at-line-9-cant-reopen-table-temp-table
			 */
			if ( !$res && $dbw->lastErrno() === 1137 ) {
				$rows = $dbw->select(
					$this->tableName,
					array( 'tree_depth', 'tree_ancestor_id' ),
					array( 'tree_descendant_id' => $ancestor->getBinary() ),
					__METHOD__
				);

				$res = true;
				foreach ( $rows as $row ) {
					$res &= $dbw->insert(
						$this->tableName,
						array(
							'tree_descendant_id' => $descendant->getBinary(),
							'tree_ancestor_id' => $row->tree_ancestor_id,
							'tree_depth' => $row->tree_depth + 1,
						),
						__METHOD__
					);
				}
			}
		}

		if ( !$res ) {
			$this->cache->delete( $parentKey );
			$this->cache->delete( $pathKey );
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
			$value[$descendant->getAlphadecimal()] = $descendant;
			return $value;
		};

		// This could be pretty slow if there is contention
		foreach ( $rootPath as $subtreeRoot ) {
			$cacheKey = $this->cacheKey( 'subtree', $subtreeRoot );
			$success = $this->cache->merge( $cacheKey, $callback );

			// $success is always true if bufferCache starts with begin()
			// if we failed to CAS new data, kill the cached value so it'll be
			// re-fetched from DB
			if ( !$success ) {
				$this->cache->delete( $cacheKey );
			}
		}
	}

	public function findParent( UUID $descendant ) {
		$map = $this->fetchParentMap( array( $descendant ) );
		return isset( $map[$descendant->getAlphadecimal()] ) ? $map[$descendant->getAlphadecimal()] : null;
	}

	/**
	 * Given a list of nodes, find the path from each node to the root of its tree.
	 * the root must be the first element of the array, $node must be the last element.
	 * @param UUID[] $descendants Array of UUID objects to find the root paths for.
	 * @return UUID[][] Associative array, key is the post ID in hex, value is the path as an array.
	 */
	public function findRootPaths( array $descendants ) {
		// alphadecimal => cachekey
		$cacheKeys = array();
		// alphadecimal => cache result ( distance => parent uuid obj )
		$cacheValues = array();
		// list of binary values for db query
		$missingValues = array();
		// alphadecimal => distance => parent uuid obj
		$paths = array();

		foreach( $descendants as $descendant ) {
			$cacheKeys[$descendant->getAlphadecimal()] = $this->cacheKey( 'rootpath', $descendant );
		}

		$cacheResult = $this->cache->getMulti( array_values( $cacheKeys ) );
		foreach( $descendants as $descendant ) {
			$alpha = $descendant->getAlphadecimal();
			if ( isset( $cacheResult[$cacheKeys[$alpha]] ) ) {
				$cacheValues[$alpha] = $cacheResult[$cacheKeys[$alpha]];
			} else {
				$missingValues[] = $descendant->getBinary();
				$paths[$alpha] = array();
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
				'tree_descendant_id' => $missingValues,
			),
			__METHOD__
		);

		if ( !$res || $res->numRows() === 0 ) {
			return $cacheValues;
		}

		foreach ( $res as $row ) {
			$alpha = UUID::create( $row->tree_descendant_id )->getAlphadecimal();
			$paths[$alpha][$row->tree_depth] = UUID::create( $row->tree_ancestor_id );
		}

		foreach( $paths as $descendantId => &$path ) {
			if ( !$path ) {
				$path = null;
				continue;
			}

			// sort by reverse distance, so furthest away
			// parent (root) is at position 0.
			ksort( $path );
			$path = array_reverse( $path );

			$this->cache->set( $cacheKeys[$descendantId], $path );
		}

		return $paths + $cacheValues;
	}

	/**
	 * Finds the root path for a single post ID.
	 * @param  UUID   $descendant Post ID
	 * @return UUID[]|null Path to the root of that node.
	 */
	public function findRootPath( UUID $descendant ) {
		$paths = $this->findRootPaths( array( $descendant ) );

		return isset( $paths[$descendant->getAlphadecimal()] ) ? $paths[$descendant->getAlphadecimal()] : null;
	}

	/**
	 * Finds the root posts of a list of posts.
	 * @param  UUID[]  $descendants Array of PostRevision objects to find roots for.
	 * @return UUID[] Associative array of post ID (as hex) to UUID object representing its root.
	 */
	public function findRoots( array $descendants ) {
		$paths = $this->findRootPaths( $descendants );
		$roots = array();

		foreach( $descendants as $descendant ) {
			$alpha = $descendant->getAlphadecimal();
			if ( isset( $paths[$alpha] ) ) {
				$roots[$alpha] = $paths[$alpha][0];
			}
		}

		return $roots;
	}

	/**
	 * Given a specific child node find the associated root node
	 *
	 * @param UUID $descendant
	 * @return UUID
	 * @throws DataModelException
	 */
	public function findRoot( UUID $descendant ) {
		// To simplify caching we will work through the root path instead
		// of caching our own value
		$path = $this->findRootPath( $descendant );
		if ( !$path ) {
			throw new DataModelException( $descendant->getAlphadecimal().' has no root post. Probably is a root post.', 'process-data' );
		}

		$root = array_shift( $path );

		return $root;
	}

	/**
	 * Fetch a node and all its descendants.
	 *
	 * @param UUID|UUID[] $roots
	 * @return array Multi-dimensional tree
	 * @throws DataModelException When invalid data is received from self::fetchSubtreeNodeList
	 */
	public function fetchSubtreeIdentityMap( $roots ) {
		$roots = ObjectManager::makeArray( $roots );
		if ( !$roots ) {
			return array();
		}
		$nodes = $this->fetchSubtreeNodeList( $roots );
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
		if ( !isset( $identityMap[$root->getAlphadecimal()] ) ) {
			throw new DataModelException( 'No root exists in the identityMap', 'process-data' );
		}

		return $identityMap[$root];
	}

	public function fetchFullTree( UUID $nodeId ) {
		return $this->fetchSubtree( $this->findRoot( $nodeId ) );
	}

	/**
	 * Return the id's of all nodes which are a descendant of provided roots
	 *
	 * @param UUID[] $roots
	 * @return array map from root id to its descendant list
	 */
	public function fetchSubtreeNodeList( array $roots ) {
		$list = new MultiGetList( $this->cache );
		$res = $list->get(
			array( 'tree', 'subtree' ),
			$roots,
			array( $this, 'fetchSubtreeNodeListFromDb' )
		);
		if ( $res === false ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Failure fetching node list from cache' );
			return false;
		}
		// $idx is a binary UUID
		$retval = array();
		foreach ( $res as $idx => $val ) {
			$retval[UUID::create( $idx )->getAlphadecimal()] = $val;
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
			wfDebugLog( 'Flow', __METHOD__ . ': Failure fetching node list from database' );
			return false;
		}
		if ( !$res ) {
			return array();
		}
		$nodes = array();
		foreach ( $res as $node ) {
			$ancestor = UUID::create( $node->tree_ancestor_id );
			$descendant = UUID::create( $node->tree_descendant_id );
			$nodes[$ancestor->getAlphadecimal()][$descendant->getAlphadecimal()] = $descendant;
		}

		return $nodes;
	}

	/**
	 * Fetch the id of the immediate parent node of all ids in $nodes.  Non-existent
	 * nodes are not represented in the result set.
	 */
	public function fetchParentMap( array $nodes ) {
		$list = new MultiGetList( $this->cache );
		return $list->get(
			array( 'tree', 'parent' ),
			$nodes,
			array( $this, 'fetchParentMapFromDb' )
		);
	}

	/**
	 * @param UUID[] $nodes
	 * @return UUID[]
	 * @throws \Flow\Exception\DataModelException
	 */
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
			$result[$descendant->getAlphadecimal()] = UUID::create( $node->tree_ancestor_id );
		}
		foreach ( $nodes as $node ) {
			if ( !isset( $result[$node->getAlphadecimal()] ) ) {
				// $node is a root, it has no parent
				$result[$node->getAlphadecimal()] = null;
			}
		}

		return $result;
	}
}
