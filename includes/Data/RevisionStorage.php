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
	 * The revision columns allowed to be updated
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

	// This is to prevent 'Update not allowed on xxx' error during moderation when
	// * old cache is not purged and still holds obsolete deleted column
	// * old cache is not purged and doesn't have the newly added column
	// @Todo - This may not be necessary anymore since we don't update historical
	// revisions ( flow_revision ) during moderation
	protected static $obsoleteUpdateColumns = array (
		'tree_orig_user_text',
		'rev_user_text',
		'rev_edit_user_text',
		'rev_mod_user_text',
		'rev_type_id',
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
	 * Get the column to join with flow_revision.rev_id, empty string for none
	 * @return string
	 */
	protected function joinField() {
		return '';
	}

	/**
	 * Insert to joinTable() upon revision insert
	 * @param array $row
	 * @return array
	 */
	protected function insertRelated( array $row ) {
		return $row;
	}

	/**
	 * Update to joinTable() upon revision update
	 * @param array $changes
	 * @param array $old
	 * @return array
	 */
	protected function updateRelated( array $changes, array $old ) {
		return $changes;
	}

	/**
	 * Remove from joinTable upone revision delete
	 * @param array $row
	 * @return bool
	 */
	protected function removeRelated( array $row ) {
		return true;
	}

	/**
	 * The revision type
	 * @return string
	 */
	abstract protected function getRevType();

	/**
	 * @param DbFactory $dbFactory
	 * @param array|false List of externel store servers available for insert
	 *  or false to disable. See $wgFlowExternalStore.
	 */
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

		// Add rev_type if rev_type_id exists in query condition
		$attributes = $this->addRevTypeToQuery( $attributes );

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

	protected function addRevTypeToQuery( $query ) {
		if ( isset( $query['rev_type_id'] ) ) {
			$query['rev_type'] = $this->getRevType();
		}
		return $query;
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
		$queriedKeys = array_keys( reset( $queries ) );
		// The findMulti doesn't map well to SQL, basically we are asking to answer a bunch
		// of queries. We can optimize those into a single query in a few select instances:
		if ( isset( $options['LIMIT'] ) && $options['LIMIT'] == 1 ) {
			// Find by primary key
			if ( $options == array( 'LIMIT' => 1 ) &&
				$queriedKeys === array( 'rev_id' )
			) {
				return $this->findRevId( $queries );
			}

			// Find most recent revision of a number of posts
			if ( !isset( $options['OFFSET'] ) &&
				$queriedKeys == array( 'rev_type_id' ) &&
				isset( $options['ORDER BY'] ) &&
				$options['ORDER BY'] === array( 'rev_id DESC' )
			) {
				return $this->findMostRecent( $queries );
			}
		}

		// Fetch a list of revisions for each post
		// @todo this is slow and inefficient.  Mildly better solution would be if
		// the index can ask directly for just the list of rev_id instead of whole rows,
		// but would still have the need to run a bunch of queries serially.
		if ( count( $options ) === 2 &&
			isset( $options['LIMIT'], $options['ORDER BY'] ) &&
			$options['ORDER BY'] === array( 'rev_id DESC' )
		) {
			return $this->fallbackFindMulti( $queries, $options );
		// unoptimizable query
		} else {
			wfDebugLog( 'Flow', __METHOD__
				. ': Unoptimizable query for keys: '
				. implode( ',', array_keys( $queriedKeys ) )
				. ' with options '
				. \FormatJson::encode( $options )
			);
			return $this->fallbackFindMulti( $queries, $options );
		}
	}

	protected function findRevId( array $queries ) {
		$duplicator = new ResultDuplicator( array( 'rev_id' ), 1 );
		$pks = array();
		foreach ( $queries as $idx => $query ) {
			$query = UUID::convertUUIDs( $query );
			$id = $query['rev_id'];
			$duplicator->add( $query, $idx );
			$pks[$id] = $id;
		}

		return $this->findRevIdReal( $duplicator, $pks );
	}

	protected function findMostRecent( array $queries ) {
		// SELECT MAX( rev_id ) AS rev_id
		// FROM flow_tree_revision
		// WHERE rev_type= 'post' AND rev_type_id IN (...)
		// GROUP BY rev_type_id
		$duplicator = new ResultDuplicator( array( 'rev_type_id' ), 1 );
		foreach ( $queries as $idx => $query ) {
			$duplicator->add( UUID::convertUUIDs( $query ), $idx );
		}

		$dbr = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbr->select(
			array( 'flow_revision' ),
			array( 'rev_id' => "MAX( 'rev_id' )" ),
			array( 'rev_type' => $this->getRevType() ) + $this->preprocessSqlArray( $this->buildCompositeInCondition( $dbr, $duplicator->getUniqueQueries() ) ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_type_id' )
		);
		if ( !$res ) {
			// TODO: dont fail, but dont end up caching bad result either
			throw new DataModelException( 'query failure', 'process-data' );
		}

		$revisionIds = array();
		foreach ( $res as $row ) {
			$revisionIds[] = $row->rev_id;
		}

		// Due to the grouping and max, we cant reliably get a full
		// columns info in the above query, forcing the join below
		// rather than just querying flow_revision.
		return $this->findRevIdReal( $duplicator, $revisionIds );
	}

	protected function findRevIdReal( ResultDuplicator $duplicator, array $revisionIds ) {
		if ( $revisionIds ) {
			//  SELECT * from flow_revision
			//	  JOIN flow_tree_revision ON tree_rev_id = rev_id
			//   WHERE rev_id IN (...)
			$dbr = $this->dbFactory->getDB( DB_MASTER );

			$tables = array( 'flow_revision' );
			$joins  = array();
			if ( $this->joinTable() ) {
				$tables['rev'] = $this->joinTable();
				$joins = array( 'rev' => array( 'JOIN', "rev_id = " . $this->joinField() ) );
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
				$row = (array)$row;
				$duplicator->merge( $row, array( $row ) );
			}
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
			if ( $source === null ) {
				// unanswered queries return null
				continue;
			}
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
		$rev = $this->splitUpdate( $row, 'rev' );
		$rev = $this->processExternalStore( $rev );

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

	protected function processExternalStore( array $row ) {
		// Check if we need to insert new content
		if ( $this->externalStore && !isset( $row['rev_content_url'] ) ) {
			$row = $this->insertExternalStore( $row );
		}

		// If a content url is available store that in the db
		// instead of real content.
		if ( isset( $row['rev_content_url'] ) ) {
			$row['rev_content'] = $row['rev_content_url'];
		}
		unset( $row['rev_content_url'] );

		return $row;
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
		$rev = $this->processExternalStore( $rev );

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
