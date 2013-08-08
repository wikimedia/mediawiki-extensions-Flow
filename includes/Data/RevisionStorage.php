<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\DbFactory;
use Flow\Repository\TreeRepository;
use DatabaseBase;
use ExternalStore;
use User;

abstract class RevisionStorage implements WritableObjectStorage {
	static protected $allowedUpdateColumns = array( 'rev_flags' );
	protected $dbFactory;
	protected $externalStores;

	abstract protected function joinTable();
	abstract protected function relatedPk();
	abstract protected function joinField();

	abstract protected function insertRelated( array $row, array $related );
	abstract protected function updateRelated( array $rev, array $related );
	abstract protected function removeRelated( array $row );

	public function __construct( DbFactory $dbFactory, $externalStore ) {
		$this->dbFactory = $dbFactory;
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
		$res = $dbr->select(
			array( $this->joinTable(), 'rev' => 'flow_revision' ),
			'*',
			UUID::convertUUIDs( $attributes ),
			__METHOD__,
			$options,
			array( 'rev' => array( 'JOIN', $this->joinField() . ' = rev_id' ) )
		);
		if ( !$res ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $row ) {
			$retval[$row->rev_id] = (array) $row;
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
		return $this->mergeExternalContent( $res );
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
		$joinField = $this->joinField();
		if ( $options['LIMIT'] === 1 &&
			!isset( $options['OFFSET'] ) &&
			count( $queriedKeys ) === 1 &&
			in_array( reset( $queriedKeys ), array( 'rev_id', $this->joinField() ) ) &&
			isset( $options['ORDER BY'] ) && count( $options['ORDER BY'] ) === 1 &&
			in_array( reset( $options['ORDER BY'] ), array( 'rev_id DESC', "{$this->joinField()} DESC" ) )
		) {
			return $this->findMostRecent( $queries );
		}

		return $this->fallbackFindMulti( $queries, $options );
	}

	protected function findMostRecent( array $queries ) {
		// SELECT MAX(tree_rev_id) AS tree_rev_id
		//   FROM flow_tree_revision
		//  WHERE tree_rev_descendant_id IN (...)
		//  GROUP BY tree_rev_descendant_id
		//
		//  Could we instead use this?
		//
		//  SELECT rev.*
		//	FROM flow_tree_revision rev
		//	JOIN ( SELECT MAX(tree_rev_id) as tree_rev_id
		//			 FROM flow_tree_revision
		//			WHERE tree_rev_descendant_id IN (...)
		//			GROUP BY tree_rev_descendant_id
		//		 ) max ON max.tree_rev_id = rev.tree_rev_id
		//
		$duplicator = new ResultDuplicator( array_keys( reset( $queries ) ), 1 );
		foreach ( $queries as $idx => $query ) {
			$duplicator->add( $query, $idx );
		}

		$dbr = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbr->select(
			$this->joinTable(),
			array( $joinField => "MAX( {$this->joinField()} )" ),
			$this->buildCompositeInCondition( $dbr, $duplicator->getUniqueQueries() ),
			__METHOD__,
			array( 'GROUP BY' => $keys )
		);
		if ( !$res ) {
			// TODO: dont fail, but dont end up caching bad result either
			throw new \Exception( 'query failure' );
		}

		$revisionIds = array();
		foreach ( $res as $row ) {
			$revisionIds[] = $row->$joinField;
		}

		// Due to the grouping and max, we cant reliably get a full
		// columns info in the above query, forcing the join below
		// rather than just querying flow_revision.

		//  SELECT * from flow_tree_revision
		//	  JOIN flow_revision ON tree_rev_id = rev_id
		//   WHERE tree_rev_id IN (...)
		$res = $dbr->select(
			array( 'flow_revision', 'rev' => $this->joinTable() ),
			'*',
			array( 'rev_id' => $revisionIds ),
			__METHOD__,
			array(),
			array( 'rev' => array( 'JOIN', "rev_id = $joinField" ) )
		);
		if ( !$res ) {
			// TODO: dont fail, but dont end up caching bad result either
			throw new \Exception( 'query failure' );
		}

		$result = array();
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
	protected function mergeExternalContent( array $cacheResult ) {
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

	/**
	 * We take $row as a reference because we *must* be able to effect the cached content
	 * of $row.  This is specifically required so that external store can change the content
	 * into a url pointing to the content and adjust its flags.  The other alternative would
	 * be to invent an onBeforeInsert lifecycle event.
	 */
	public function insert( array $row ) {
		// Check if we need to insert new content
		$row = $this->handleContentInsertion( $row );
		list( $rev, $related ) = $this->splitUpdate( $row );
		// If a content url is available store that in the db
		// instead of real content.
		if ( isset( $rev['rev_content_url'] ) ) {
			$rev['rev_content'] = $rev['rev_content_url'];
		}
		unset( $rev['rev_content_url'] );

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			'flow_revision',
			$rev,
			__METHOD__
		);
		if ( !$res ) {
			// throw exception?
			return false;
		}

		return $this->insertRelated( $row, $related );
	}

	protected function handleContentInsertion( array $row ) {
		if ( isset( $row['rev_content_url'] ) ) {
			// Content already exists
			return $row;
		}

		$flags = array_unique( array_merge(
			explode( ',', $row['rev_flags'] ),
			explode( ',', \Revision::compressRevisionText( $row['rev_content'] ) )
		) );

		if ( $this->externalStore ) {
			$url = ExternalStore::insertWithFallback( $this->externalStore, $row['rev_content'] );
			if ( !$url ) {
				throw new \MWException( "Unable to store text to external storage" );
			}
			$flags[] = 'external';
			$row['rev_content_url'] = $url;
		}

		$row['rev_flags'] = implode( ',', $flags );
		return $row;
	}

	// This is to *UPDATE* a revision.  It should hardly ever be used.
	// For the most part should insert a new revision.  This will only be called
	// for oversighting?
	public function update( array $row, array $changeSet ) {
		$extra = array_diff( array_keys( $changeSet ), self::$allowedUpdateColumns );
		if ( $extra ) {
			throw new \MWException( 'Update not allowed on: ' . implode( ', ', $extra ) );
		}

		list( $rev, $related ) = $this->splitUpdate( $changeSet );

		if ( $rev ) {
			$dbw = $this->dbFactory->getDB( DB_MASTER );
			$res = $dbw->update(
				'flow_revision',
				$updates,
				array( 'rev_id' => $row['rev_id'] ),
				__METHOD__
			);
			if ( !( $res && $res->numRows() ) ) {
				return false;
			}
		}
		return $this->updateRelated( $rev, $related );
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
		if ( !( $res && $res->numRows() ) ) {
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
		throw new \MWException( 'Not Implemented' );
	}

	// Separates $row into two arrays, one with the rev_ prefix
	// and the other with everything else.  May need to split more
	// specifically if we want > 2 prefixes.
	protected function splitUpdate( array $row ) {
		$rev = $related = array();
		foreach ( $row as $key => $value ) {
			$prefix = substr( $key, 0, 4 );
			if ( $prefix === 'rev_' ) {
				$rev[$key] = $value;
			} else {
				$related[$key] = $value;
			}
		}
		return array( $rev, $related );
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

	protected function insertRelated( array $row, array $tree ) {
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			$this->joinTable(),
			UUID::convertUUIDs( $tree ),
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
	protected function updateRelated( array $row, array $treeChanges ) {
		if ( $treeChanges ) {
			throw new \MWException( 'Update not allowed' );
		}
	}

	// this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, its just a tree
	// and we arn't going to re-parent its children;
	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			array( $this-joinField() => $row['rev_id'] )
		);
	}
}

class SummaryRevisionStorage extends RevisionStorage {

	protected function joinTable() {
		return 'flow_summary_revision';
	}

	protected function relatedPk() {
		return 'summary_workflow_id';
	}

	protected function joinField() {
		return 'summary_rev_id';
	}

	protected function insertRelated( array $row, array $summary ) {
		$res = $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->joinTable(),
			$summary,
			__METHOD__
		);
		if ( !$res ) {
			return false;
		}
		return $row;
	}

	// There is changable data in the summary half, it just points to the correct workflow
	protected function updateRelated( array $rev, array $summaryChanges ) {
		if ( $summaryChanges ) {
			throw new \MWException( 'No update allowed' );
		}
	}

	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			array( $this-joinField() => $row['rev_id'] )
		);
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
			if ( $name === $fromKey ) {
				$source[$idx]["{$name}_url"] = $source[$idx][$name];
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
				if ( $name === $fromKey ) {
					$multiSource[$i][$j]["{$name}_url"] = $id;
				}
				$multiSource[$i][$j][$name] = isset( $res[$id] ) ? $res[$id] : $default;
			}
		}
		return $multiSource;
	}
}
