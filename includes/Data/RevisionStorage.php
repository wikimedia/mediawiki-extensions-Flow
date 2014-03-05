<?php

namespace Flow\Data;

use DatabaseBase;
use ExternalStore;
use Flow\DbFactory;
use Flow\Exception\DataModelException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use MWException;

abstract class RevisionStorage extends DbStorage {

	/**
	 * The columns allowed to update
	 * @var array
	 */
	protected static $allowedUpdateColumns = array(
		'rev_mod_state',
		'rev_mod_user_id',
		'rev_mod_user_ip',
		'rev_mod_user_wiki',
		'rev_mod_timestamp',
		'rev_mod_reason',
	);

	/** 
	 * This is to prevent 'Update not allowed on xxx error' when
	 * (a) obsolete columns have not been removed
	 * (b) new columns are added, but code is not deployed yet
	 */ 
	protected static $obsoleteUpdateColumns = array (
		// Obsolete columns
		'tree_orig_user_text',
		'rev_user_text',
		'rev_edit_user_text',
		'rev_mod_user_text',
	);

	protected $externalStore;

	/**
	 * Get the table to join for the revision storage, empty string for none 
	 * @return string
	 */
	protected function joinTable() {
		return '';
	}

	/**
	 * Get the primary key of the join table, empty string for none
	 * @return string
	 */
	protected function relatedPk() {
		return '';
	}

	/**
	 * Get the column to join with flow_revision.rev_id, empty string for none
	 * @return string
	 */
	protected function joinField() {
		return '';
	}

	/**
	 * Insert to the joinTable() upon revision insert
	 * @param array $row
	 * @return array $row
	 */
	protected function insertRelated( array $row ) {
		return $row;
	}

	/**
	 * Update to the joinTable() upon revision update
	 * @param array $changes
	 * @param array $old
	 * @return array $changes
	 */
	protected function updateRelated( array $changes, array $old ) {
		return $changes;	
	}

	/**
	 * Remove from joinTable upone revision delete
	 * @param array $row
	 * @return bool|ResultWrapper
	 */
	protected function removeRelated( array $row ) {
		return true;
	}

	/**
	 * Get the revision type
	 * @return string
	 */
	protected abstract function getRevisionType();

	public function __construct( DbFactory $dbFactory, $externalStore ) {
		parent::__construct( $dbFactory );
		$this->externalStore = $externalStore;
	}

	// Find one by specific attributes
	// @todo: this method can probably be generalized in parent class?
	public function find( array $attributes, array $options = array() ) {
		$multi = $this->findMulti( array( $attributes ), $options );
		if ( $multi ) {
			return reset( $multi );
		}
		return null;
	}

	protected function findInternal( array $attributes, array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_MASTER );

		if ( ! $this->validateOptions( $options ) ) {
			throw new MWException( "Validation error in database options" );
		}

		$tables = array( 'rev' => 'flow_revision' );
		$joins = array();
		if ( $this->joinTable() ) {
			$tables[] = $this->joinTable();
			$joins = array( 'rev' => array( 'JOIN', $this->joinField() . ' = rev_id' ) );
		}
		
		$res = $dbr->select(
			$tables, '*', $this->preprocessSqlArray( $attributes ), __METHOD__, $options, $joins
		);
		if ( !$res ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $row ) {
			$retval[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
		}
		return $retval;
	}

	public function findMulti( array $queries, array $options = array() ) {
		if ( count( $queries ) < 3 ) {
			$res = $this->fallbackFindMulti( $queries, $options );
		} else {
			$res = $this->findMultiInternal( $queries, $options );
		}
		// Fetches content for all revisions flagged 'external'
		return self::mergeExternalContent( $res );
	}

	protected function fallbackFindMulti( array $queries, array $options ) {
		$result = array();
		foreach ( $queries as $key => $attributes ) {
			$result[$key] = $this->findInternal( $attributes, $options );
		}
		return $result;
	}

	protected function findMultiInternal( array $queries, array $options = array() ) {
		// The findMulti doesn't map well to SQL, basically we are asking to answer a bunch
		// of queries. We can optimize those into a single query in a few select instances:
		// Either
		//   All queries are feature queries for a unique value
		// OR
		//   queries have limit 1
		//   queries have no offset
		//   queries are sorted by the join field(which is time sorted)
		//   query keys are all in the related table and not the revision table
		//
		$queriedKeys = array_keys( reset( $queries ) );
		if ( $options['LIMIT'] === 1 &&
			!isset( $options['OFFSET'] ) &&
			count( $queriedKeys ) === 1 &&
			in_array( 'rev_type', $queriedKeys ) &&
			in_array( 'rev_type_id', $queriedKeys ) &&
			isset( $options['ORDER BY'] ) && count( $options['ORDER BY'] ) === 1 &&
			in_array( reset( $options['ORDER BY'] ), array( 'rev_id DESC', "{$this->joinField()} DESC" ) )
		) {
			return $this->findMostRecent( $queries );
		}

		return $this->fallbackFindMulti( $queries, $options );
	}

	protected function findMostRecent( array $queries ) {
		$duplicator = new ResultDuplicator( array_keys( reset( $queries ) ), 1 );
		foreach ( $queries as $idx => $query ) {
			$duplicator->add( UUID::convertUUIDs( $query ), $idx );
		}

		$dbr = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbr->select(
			array( 'flow_revision' ),
			array( 'rev_id' => "MAX( 'rev_id' )" ),
			array( 'rev_type' => $this->getRevisionType() ) + $this->preprocessSqlArray( $this->buildCompositeInCondition( $dbr, $duplicator->getUniqueQueries() ) ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_type, rev_type_id' )
		);
		if ( !$res ) {
			// TODO: dont fail, but dont end up caching bad result either
			throw new DataModelException( 'query failure', 'process-data' );
		}

		$revisionIds = array();
		foreach ( $res as $row ) {
			$revisionIds[] = $row->rev_id;
		}

		$tables = array( 'flow_revision' );
		$joins  = array();
		if ( $this->joinTable() ) {
			$tables['rev'] = $this->joinTable();
			$joins = array( 'rev' => array( 'JOIN', "rev_id = $joinField" ) ); 
		}
		$res = $dbr->select(
			$tables,
			'*',
			array( 'rev_id' => $revisionIds ),
			__METHOD__,
			array(),
			$joins
		);
		if ( !$res ) {
			// TODO: dont fail, but dont end up caching bad result either
			throw new DataModelException( 'query failure', 'process-data' );
		}

		foreach ( $res as $row ) {
			$row = (array) $row;
			$duplicator->merge( $row, array( $row ) );
		}

		return $duplicator->getResult();
	}

	/**
	 * Handle the injection of externalstore data into a revision
	 * row.  All rows exiting this method will have rev_content_url
	 * set to either null or the external url.  The rev_content
	 * field will be the final content (possibly compressed still)
	 *
	 * @param array $cacheResult 2d array of rows
	 * @return array 2d array of rows with content merged and rev_content_url populated
	 */
	public static function mergeExternalContent( array $cacheResult ) {
		foreach ( $cacheResult as &$source ) {
			foreach ( $source as &$row ) {
				$flags = explode( ',', $row['rev_flags'] );
				if ( in_array( 'external', $flags ) ) {
					$row['rev_content_url'] = $row['rev_content'];
					$row['rev_content'] = '';
				} else {
					$row['rev_content_url'] = null;
				}
			}
		}

		return Merger::mergeMulti(
			$cacheResult,
			/* fromKey = */ 'rev_content_url',
			/* callable = */ array( 'ExternalStore', 'batchFetchFromURLs' ),
			/* name = */ 'rev_content',
			/* default = */ ''
		);
	}

	protected function buildCompositeInCondition( DatabaseBase $dbr, array $queries ) {
		$keys = array_keys( reset( $queries ) );
		$conditions = array();
		if ( count( $keys ) === 1 ) {
			// standard in condition: tree_rev_descendant_id IN (1,2...)
			$key = reset( $keys );
			foreach ( $queries as $query ) {
				$conditions[$key][] = reset( $query );
			}
			return $conditions;
		} else {
			// composite in condition: ( foo = 1 AND bar = 2 ) OR ( foo = 1 AND bar = 3 )...
			// Could be more efficient if composed as a range scan, but seems more complex than
			// its benefit.
			foreach ( $queries as $query ) {
				$conditions[] = $dbr->makeList( $query, LIST_AND );
			}
			return $dbr->makeList( $conditions, LIST_OR );
		}
	}

	public function insert( array $row ) {
		// Check if we need to insert new content
		if ( $this->externalStore && !isset( $row['rev_content_url'] ) ) {
			$row = $this->insertExternalStore( $row );
		}
		$rev = $this->splitUpdate( $row, 'rev' );
		// If a content url is available store that in the db
		// instead of real content.
		if ( isset( $rev['rev_content_url'] ) ) {
			$rev['rev_content'] = $rev['rev_content_url'];
		}
		unset( $rev['rev_content_url'] );

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			'flow_revision',
			$this->preprocessSqlArray( $rev ),
			__METHOD__
		);
		if ( !$res ) {
			// throw exception?
			return false;
		}

		return $this->insertRelated( $row );
	}

	protected function insertExternalStore( array $row ) {
		$url = ExternalStore::insertWithFallback( $this->externalStore, $row['rev_content'] );
		if ( !$url ) {
			throw new DataModelException( "Unable to store text to external storage", 'process-data' );
		}
		$row['rev_content_url'] = $url;
		if ( $row['rev_flags'] ) {
			$row['rev_flags'] .= ',external';
		} else {
			$row['rev_flags'] = 'external';
		}

		return $row;
	}

	// This is to *UPDATE* a revision.  It should hardly ever be used.
	// For the most part should insert a new revision.  This will only be called
	// for suppressing?
	public function update( array $old, array $new ) {
		$changeSet = ObjectManager::calcUpdates( $old, $new );

		foreach( static::$obsoleteUpdateColumns as $val ) {
			// Need to use array_key_exists to check null value
			if ( array_key_exists( $val, $changeSet ) ) {
				unset( $changeSet[$val] );
			}
		}

		$extra = array_diff( array_keys( $changeSet ), static::$allowedUpdateColumns );
		if ( $extra ) {
			throw new DataModelException( 'Update not allowed on: ' . implode( ', ', $extra ), 'process-data' );
		}

		$rev = $this->splitUpdate( $changeSet, 'rev' );

		if ( $rev ) {
			$dbw = $this->dbFactory->getDB( DB_MASTER );
			$res = $dbw->update(
				'flow_revision',
				$this->preprocessSqlArray( $rev ),
				array( 'rev_id' => $old['rev_id'] ),
				__METHOD__
			);
			if ( !( $res && $dbw->affectedRows() ) ) {
				return false;
			}
		}
		return $this->updateRelated( $changeSet, $old );
	}


	// Revisions can only be removed for LIMITED circumstances,  in almost all cases
	// the offending revision should be updated with appropriate suppression.
	// Also note this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, it will just show up as
	// [deleted] or something
	public function remove( array $row ) {
		$res = $this->dbFactory->getDB( DB_MASTER )->delete(
			'flow_revision',
			array( 'rev_id' => $row['rev_id'] ),
			__METHOD__
		);
		if ( !$res ) {
			return false;
		}
		return $this->removeRelated( $row );
	}

	/**
	 * Used to locate the index for a query by ObjectLocator::get()
	 */
	public function getPrimaryKeyColumns() {
		return array( 'rev_id' );
	}

	public function getIterator() {
		throw new DataModelException( __CLASS__ . '::' . __METHOD__ . ' is not implemented', 'process-data' );
	}

	/**
	 * Gets all columns from $row that start with a given prefix and omits other
	 * columns.
	 *
	 * @param array $row Rows to split
	 * @param string[optional] $prefix
	 * @return array Remaining rows
	 */
	protected function splitUpdate( array $row, $prefix = 'rev' ) {
		$rev = array();
		foreach ( $row as $key => $value ) {
			$keyPrefix = strstr( $key, '_', true );
			if ( $keyPrefix === $prefix ) {
				$rev[$key] = $value;
			}
		}
		return $rev;
	}
}

class PostRevisionStorage extends RevisionStorage {

	public function __construct( DbFactory $dbFactory, $externalStore, TreeRepository $treeRepo ) {
		parent::__construct( $dbFactory, $externalStore );
		$this->treeRepo = $treeRepo;
	}

	protected function joinTable() {
		return 'flow_tree_revision';
	}

	protected function relatedPk() {
		return 'tree_rev_descendant_id';
	}

	protected function joinField() {
		return 'tree_rev_id';
	}

	protected function getRevisionType() {
		return 'post';
	}

	protected function insertRelated( array $row ) {
		$tree = $this->splitUpdate( $row, 'tree' );
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			$this->joinTable(),
			$this->preprocessSqlArray( $tree ),
			__METHOD__
		);

		// If this is a brand new root revision it needs to be added to the tree
		// If it has a rev_parent_id then its already a part of the tree
		if ( $res && $row['rev_parent_id'] === null ) {
			$res = (bool) $this->treeRepo->insert(
				UUID::create( $tree['tree_rev_descendant_id'] ),
				UUID::create( $tree['tree_parent_id'] )
			);
		}

		if ( !$res ) {
			return false;
		}

		return $row;
	}

	// Topic split will primarily be done through the TreeRepository directly,  but
	// we will need to accept updates to the denormalized tree_parent_id field for
	// the new root post
	protected function updateRelated( array $changes, array $old ) {
		$treeChanges = $this->splitUpdate( $changes, 'tree' );

		// no changes to be performed
		if ( !$treeChanges ) {
			return $changes;
		}

		foreach( static::$obsoleteUpdateColumns as $val ) {
			// Need to use array_key_exists to check null value
			if ( array_key_exists( $val, $treeChanges ) ) {
				unset( $treeChanges[$val] );
			}
		}

		$extra = array_diff( array_keys( $treeChanges ), static::$allowedUpdateColumns );
		if ( $extra ) {
			throw new DataModelException( 'Update not allowed on: ' . implode( ', ', $extra ), 'process-data' );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->update(
			$this->joinTable(),
			$this->preprocessSqlArray( $treeChanges ),
			array( 'tree_rev_id' => $old['tree_rev_id'] ),
			__METHOD__
		);

		if ( !$res ) {
			return false;
		}

		return $changes;
	}

	// this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, its just a tree
	// and we arn't going to re-parent its children;
	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			array( $this->joinField() => $row['rev_id'] )
		);
	}
}

class HeaderRevisionStorage extends RevisionStorage {
	protected function getRevisionType() {
		return 'header';
	}
}

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
			$nodes = $nodeList[$features['topic_root_id']->getAlphadecimal()];
			$descendantQueries[$idx] = array(
				'rev_type' => 'post', 'rev_type_id' => UUID::convertUUIDs( $nodes ),
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
/**
 * This assists in performing client-side 1-to-1 joins.  It collects the foreign key
 * from a multi-dimensional array, queries a callable for the foreign key values and
 * then returns the source data with related data merged in.
 */
class Merger {

	/**
	 * @param array    $source   input two dimensional array
	 * @param string   $fromKey  Key in nested arrays of $source containing foreign key
	 * @param callable $callable Callable receiving array of foreign keys returning map
	 *                           from foreign key to its value
	 * @param string   $name     Name to merge loaded foreign data as.  If null uses $fromKey.
	 * @param string   $default  Value to use when no matching foreign value can be located
	 * @return array $source array with all found foreign key values merged
	 */
	static public function merge( array $source, $fromKey, $callable, $name = null, $default = '' ) {
		if ( $name === null ) {
			$name = $fromKey;
		}
		$ids = array();
		foreach ( $source as $row ) {
			$id = $row[$fromKey];
			if ( $id !== null ) {
				$ids[] = $id;
			}
		}
		if ( !$ids ) {
			return $source;
		}
		$res = call_user_func( $callable, $ids );
		if ( $res === false ) {
			return false;
		}
		foreach ( $source as $idx => $row ) {
			$id = $row[$fromKey];
			if ( $id === null ) {
				continue;
			}
			$source[$idx][$name] = isset( $res[$id] ) ? $res[$id] : $default;
		}
		return $source;
	}

	/**
	 * Same as self::merge, but for 3-dimensional source arrays
	 */
	static public function mergeMulti( array $multiSource, $fromKey, $callable, $name = null, $default = '' ) {
		if ( $name === null ) {
			$name = $fromKey;
		}
		$ids = array();
		foreach ( $multiSource as $source ) {
			foreach ( $source as $row ) {
				$id = $row[$fromKey];
				if ( $id !== null ) {
					$ids[] = $id;
				}
			}
		}
		if ( !$ids ) {
			return $multiSource;
		}
		$res = call_user_func( $callable, array_unique( $ids ) );
		if ( $res === false ) {
			return false;
		}
		foreach ( $multiSource as $i => $source ) {
			foreach ( $source as $j => $row ) {
				$id = $row[$fromKey];
				if ( $id === null ) {
					continue;
				}
				$multiSource[$i][$j][$name] = isset( $res[$id] ) ? $res[$id] : $default;
			}
		}
		return $multiSource;
	}
}
