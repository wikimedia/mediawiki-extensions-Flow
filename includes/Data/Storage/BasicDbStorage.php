<?php

namespace Flow\Data\Storage;

use Flow\Model\UUID;
use Flow\DbFactory;
use Flow\Data\ObjectManager;
use Flow\Data\Utils\MultiDimArray;
use Flow\Data\Utils\RawSql;
use Flow\Exception\DataModelException;
use Flow\Exception\DataPersistenceException;

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
	 * @param  array  $rows The rows to insert. Also accepts a single row.
	 * @return array|false  An array of the rows that now exist
	 * in the database. Integrity of keys is guaranteed.
	 * False if we failed.
	 */
	public function insert( array $rows ) {
		// Only allow the row to include key/value pairs.
		// No raw SQL.
		if ( is_array( reset( $rows ) ) ) {
			$insertRows = $this->preprocessNestedSqlArray( $rows );
		} else {
			$insertRows = $this->preprocessSqlArray( $rows );
		}

		// insert returns boolean true/false
		$res = $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->table,
			$insertRows,
			__METHOD__ . " ({$this->table})"
		);
		if ( $res ) {
			return $rows;
		} else {
			return false;
		}
	}

	/**
	 * Update a single row in the database.
	 *
	 * @param  array  $old The current state of the row.
	 * @param  array  $new The desired new state of the row.
	 * @return boolean     Whether or not the operation was successful.
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

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		// update returns boolean true/false as $res
		$res = $dbw->update( $this->table, $updates, $pk, __METHOD__ . " ({$this->table})" );
		// $dbw->update returns boolean true/false as $res
		// we also want to check that $pk actually selected a row to update
		return $res && $dbw->affectedRows();
	}

	/**
	 * @param array $row
	 * @return boolean success
	 * @throws DataPersistenceException
	 */
	public function remove( array $row ) {
		$pk = ObjectManager::splitFromRow( $row, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $row ) );
			throw new DataPersistenceException( 'Row has null primary key: ' . implode( ', ', $missing ), 'process-data' );
		}

		$pk = $this->preprocessSqlArray( $pk );

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->delete( $this->table, $pk, __METHOD__ . " ({$this->table})" );
		return $res && $dbw->affectedRows();
	}

	/*
	 * @return array|null  Empty array means no result,  null means query failure.  Array with results is
	 *                     success.
	 */
	public function find( array $attributes, array $options = array() ) {
		$attributes = $this->preprocessSqlArray( $attributes );

		if ( !$this->validateOptions( $options ) ) {
			throw new \MWException( "Validation error in database options" );
		}

		$res = $this->dbFactory->getDB( DB_MASTER )->select(
			$this->table,
			'*',
			$attributes,
			__METHOD__ . " ({$this->table})",
			$options
		);
		if ( ! $res ) {
			return null;
		}

		$result = array();
		foreach ( $res as $row ) {
			$result[] = UUID::convertUUIDs( (array) $row, 'alphadecimal' );
		}
		// wfDebugLog( 'Flow', __METHOD__ . ': ' . print_r( $result, true ) );
		return $result;
	}

	protected function fallbackFindMulti( array $queries, array $options ) {
		$result = array();
		foreach ( $queries as $key => $query ) {
			$result[$key] = $this->find( $query, $options );
		}
		return $result;
	}

	public function findMulti( array $queries, array $options = array() ) {
		$keys = array_keys( reset( $queries ) );
		$pks = $this->getPrimaryKeyColumns();
		if ( count( $keys ) !== count( $pks ) || array_diff( $keys, $pks ) ) {
			return $this->fallbackFindMulti( $queries, $options );
		}
		$conds = array();
		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		foreach ( $queries as $query ) {
			$conds[] = $dbr->makeList( $this->preprocessSqlArray( $query ), LIST_AND );
		}
		unset( $query );

		$conds = $dbr->makeList( $conds, LIST_OR );

		$result = array();
		// options can be ignored for primary key search
		$res = $this->find( array( new RawSql( $conds ) ) );
		if ( !$res ) {
			return $result;
		}

		// create temp array with pk value (usually uuid) as key and full db row
		// as value
		$temp = new MultiDimArray();
		foreach ( $res as $val ) {
			$val = UUID::convertUUIDs( $val, 'alphadecimal' );
			$temp[ObjectManager::splitFromRow( $val, $this->primaryKey )] = $val;
		}

		// build return value by mapping the database rows to the matching array
		// index in $queries
		foreach ( $queries as $i => $val ) {
			$val = UUID::convertUUIDs( $val, 'alphadecimal' );
			$pk = ObjectManager::splitFromRow( $val, $this->primaryKey );
			$result[$i][] = isset( $temp[$pk] ) ? $temp[$pk] : null;
		}

		return $result;
	}

	public function getPrimaryKeyColumns() {
		return $this->primaryKey;
	}
}
