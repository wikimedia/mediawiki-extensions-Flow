<?php

namespace Flow\Data\Storage;

use Flow\Data\ObjectManager;
use Flow\Data\Utils\MultiDimArray;
use Flow\DbFactory;
use Flow\Exception\DataModelException;
use Flow\Exception\DataPersistenceException;
use Flow\Model\UUID;
use InvalidArgumentException;

/**
 * Standard backing store for data model with no special cases which is stored
 * in a single table in mysql.
 *
 * Doesn't support updating primary key value yet
 * Doesn't support auto-increment pk yet
 */
class BasicDbStorage extends DbStorage {
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var string[]
	 */
	protected $primaryKey;

	/**
	 * @param DbFactory $dbFactory
	 * @param string $table
	 * @param string[] $primaryKey
	 * @throws DataModelException
	 */
	public function __construct( DbFactory $dbFactory, $table, array $primaryKey ) {
		if ( !$primaryKey ) {
			throw new DataModelException( 'PK required', 'process-data' );
		}
		parent::__construct( $dbFactory );
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}

	/**
	 * Inserts a set of rows into the database
	 *
	 * @param array $rows The rows to insert. Also accepts a single row.
	 * @return array An array of the rows that now exist
	 * in the database. Integrity of keys is guaranteed.
	 */
	public function insert( array $rows ) {
		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( is_array( reset( $rows ) ) ) {
			$insertRows = $this->preprocessNestedSqlArray( $rows );
		} else {
			$insertRows = [ $this->preprocessSqlArray( $rows ) ];
		}
		$queryBuilder = $this->dbFactory->getDB( DB_PRIMARY )->newInsertQueryBuilder()
			->insertInto( $this->table )
			->rows( $insertRows )
			->caller( __METHOD__ . " ({$this->table})" );
		DbStorage::maybeSetInsertIgnore( $queryBuilder );
		$queryBuilder->execute();

		return $rows;
	}

	/**
	 * Update a single row in the database.
	 *
	 * @param array $old The current state of the row.
	 * @param array $new The desired new state of the row.
	 * @return bool Whether or not the operation was successful.
	 * @throws DataPersistenceException
	 */
	public function update( array $old, array $new ) {
		$pk = ObjectManager::splitFromRow( $old, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $old ) );
			throw new DataPersistenceException( 'Row has null primary key: ' . implode( ', ', $missing ), 'process-data' );
		}
		$updates = $this->calcUpdates( $old, $new );
		if ( !$updates ) {
			return true; // nothing to change, success
		}

		// Only allow the row to include key/value pairs.
		// No raw SQL.
		$updates = $this->preprocessSqlArray( $updates );
		$pk = $this->preprocessSqlArray( $pk );

		$dbw = $this->dbFactory->getDB( DB_PRIMARY );
		// update returns boolean true/false as $res
		$dbw->newUpdateQueryBuilder()
			->update( $this->table )
			->set( $updates )
			->where( $pk )
			->caller( __METHOD__ . " ({$this->table})" )
			->execute();
		// we also want to check that $pk actually selected a row to update
		return (bool)$dbw->affectedRows();
	}

	/**
	 * @param array $row
	 * @return bool success
	 * @throws DataPersistenceException
	 */
	public function remove( array $row ) {
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $row ) );
			throw new DataPersistenceException( 'Row has null primary key: ' . implode( ', ', $missing ), 'process-data' );
		}

		$pk = $this->preprocessSqlArray( $pk );

		$dbw = $this->dbFactory->getDB( DB_PRIMARY );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( $this->table )
			->where( $pk )
			->caller( __METHOD__ . " ({$this->table})" )
			->execute();
		return (bool)$dbw->affectedRows();
	}

	/**
	 * @param array $attributes
	 * @param array $options
	 * @return array Empty array means no result.  Array with results is success.
	 * @throws DataModelException On query failure
	 */
	public function find( array $attributes, array $options = [] ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		if ( !$this->validateOptions( $options ) ) {
			throw new InvalidArgumentException( "Validation error in database options" );
		}

		$dbr = $this->dbFactory->getDB( DB_REPLICA );
		$res = $this->doFindQuery( $attributes, $options );
		if ( $res === false ) {
			throw new DataModelException( __METHOD__ . ': Query failed: ' . $dbr->lastError(), 'process-data' );
		}

		$result = [];
		foreach ( $res as $row ) {
			$result[] = UUID::convertUUIDs( (array)$row, 'alphadecimal' );
		}
		return $result;
	}

	protected function doFindQuery( array $preprocessedAttributes, array $options = [] ) {
		return $this->dbFactory->getDB( DB_REPLICA )->newSelectQueryBuilder()
			->select( '*' )
			->from( $this->table )
			->where( $preprocessedAttributes )
			->caller( __METHOD__ . " ({$this->table})" )
			->options( $options )
			->fetchResultSet();
	}

	protected function fallbackFindMulti( array $queries, array $options ) {
		$result = [];
		foreach ( $queries as $key => $query ) {
			$result[$key] = $this->find( $query, $options );
		}
		return $result;
	}

	/**
	 * @param array $queries
	 * @param array $options
	 * @return array
	 * @throws DataModelException
	 */
	public function findMulti( array $queries, array $options = [] ) {
		$keys = array_keys( reset( $queries ) );
		$pks = $this->getPrimaryKeyColumns();
		if ( count( $keys ) !== count( $pks ) || array_diff( $keys, $pks ) ) {
			return $this->fallbackFindMulti( $queries, $options );
		}
		$conds = [];
		$dbr = $this->dbFactory->getDB( DB_REPLICA );
		foreach ( $queries as $query ) {
			$conds[] = $dbr->andExpr( $this->preprocessSqlArray( $query ) );
		}
		unset( $query );

		// options can be ignored for primary key search
		$res = $this->find( [ $dbr->orExpr( $conds ) ] );

		// create temp array with pk value (usually uuid) as key and full db row
		// as value
		$temp = new MultiDimArray();
		foreach ( $res as $val ) {
			$val = UUID::convertUUIDs( $val, 'alphadecimal' );
			$temp[ObjectManager::splitFromRow( $val, $this->primaryKey )] = $val;
		}

		// build return value by mapping the database rows to the matching array
		// index in $queries
		$result = [];
		foreach ( $queries as $i => $val ) {
			$val = UUID::convertUUIDs( $val, 'alphadecimal' );
			$pk = ObjectManager::splitFromRow( $val, $this->primaryKey );
			if ( isset( $temp[$pk] ) ) {
				$result[$i][] = $temp[$pk];
			}
		}

		return $result;
	}

	public function getPrimaryKeyColumns() {
		return $this->primaryKey;
	}
}
