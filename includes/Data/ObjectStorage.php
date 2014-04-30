<?php

namespace Flow\Data;

/**
 * Interface representing backend data stores.  Typically they
 * will be implemented in SQL with the DbStorage base class.
 */
interface ObjectStorage {

	/**
	 * Perform a single equality query.
	 *
	 * @param array $attributes Map of attributes the model must contain
	 * @param array $options Query options such as ORDER BY and LIMIT.
	 * @return array
	 */
	function find( array $attributes, array $options = array() );

	/**
	 * Perform the equivalent of array_map against self::find for multiple
	 * equality queries with the minimum of network round trips.
	 *
	 * @param array $queries list of queries to perform
	 * @param array $options Options to use for all queries
	 * @return array
	 */
	function findMulti( array $queries, array $options = array() );

	/**
	 * @return array The list of columns that together uniquely identify a row
	 */
	function getPrimaryKeyColumns();

	/**
	 * Insert the specified row into the data store.
	 *
	 * @param array $row Map of columns to values
	 * @return array|false The resulting $row including any auto-assigned ids or false on failure
	 */
	function insert( array $row );

	/**
	 * Perform all changes necessary to turn $old into $new in the data store.
	 *
	 * @param array $old Map of columns to values that was initially loaded.
	 * @param array $new Map of columns to values that the row should become.
	 * @return boolean true when the row is successfully updated
	 */
	function update( array $old, array $new );

	/**
	 * Remove the specified row from the data store.
	 *
	 * @param array $row Map of columns to values.  Must contain the primary key columns.
	 * @return boolean true when the row is successfully removed
	 */
	function remove( array $row );
}
