<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\DbFactory;
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
	public function __construct( DbFactory $dbFactory, $table, array $primaryKey ) {
		if ( !$primaryKey ) {
			throw new DataModelException( 'PK required', 'process-data' );
		}
		parent::__construct( $dbFactory );
		$this->table = $table;
		$this->primaryKey = $primaryKey;
	}

	// Does not support auto-increment id yet
	public function insert( array $row ) {
		// Only allow the row to include key/value pairs.
		// No raw SQL.
		$row = $this->preprocessSqlArray( $row );

		// insert returns boolean true/false
		$res = $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->table,
			$row,
			__METHOD__ . " ({$this->table})"
		);
		if ( $res ) {
			return $row;
		} else {
			return false;
		}
	}

	public function update( array $old, array $new ) {
		$pk = ObjectManager::splitFromRow( $old, $this->primaryKey );
		if ( $pk === null ) {
			$missing = array_diff( $this->primaryKey, array_keys( $old ) );
			throw new DataPersistenceException( 'Row has null primary key: ' . implode( $missing ), 'process-data' );
		}
		$updates = ObjectManager::calcUpdates( $old, $new );
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
			throw new DataPersistenceException( 'Row has null primary key: ' . implode( $missing ), 'process-data' );
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

		if ( ! $this->validateOptions( $options ) ) {
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
			$result[] = (array) $row;
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
		$pks  = $this->getPrimaryKeyColumns();
		if ( count( $keys ) !== count( $pks ) || array_diff( $keys, $pks ) ) {
			return $this->fallbackFindMulti( $queries, $options );
		}
		$conds = array();
		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		foreach ( $queries as &$query ) {
			$query = UUID::convertUUIDs( $query );
			$conds[] = $dbr->makeList( $query, LIST_AND );
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
			$temp[ObjectManager::splitFromRow( $val, $this->primaryKey )] = $val;
		}

		// build return value by mapping the database rows to the matching array
		// index in $queries
		foreach ( $queries as $i => $val ) {
			$pk = ObjectManager::splitFromRow( $val, $this->primaryKey );
			$result[$i][] = isset( $temp[$pk] ) ? $temp[$pk] : null;
		}

		return $result;
	}

	/**
	 * Only use from maintenance and debugging
	 */
	public function getIterator( $batchSize = 500 ) {
		return new \EchoBatchRowIterator(
			$this->dbFactory->getDB( DB_MASTER ),
			$this->table,
			$this->primaryKey,
			$batchSize
		);
	}

	public function getPrimaryKeyColumns() {
		return $this->primaryKey;
	}
}
