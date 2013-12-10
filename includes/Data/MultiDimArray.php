<?php

namespace Flow\Data;

use Flow\Exception\InvalidInputException;

/**
 * This object can be used to easily set keys in a multi-dimensional array.
 *
 * Usage:
 *
 *   $arr = new Flow\Data\MultiDimArray;
 *   $arr[array(1,2,3)] = 4;
 *   $arr[array(2,3,4)] = 5;
 *   var_export( $arr->all() );
 *
 *   array (
 *     1 => array (
 *       2 => array (
 *         3 => 4,
 *       ),
 *     ),
 *     2 => array (
 *       3 => array (
 *         4 => 5,
 *       ),
 *     ),
 *   )
 */
class MultiDimArray implements \ArrayAccess {
	protected $data = array();

	public function all() {
		return $this->data;
	}

	// Probably not what you want.  primary key value is lost, you only
	// receive the final key in a composite key set.
	public function getIterator() {
		$it = new RecursiveArrayIterator( $this->data );
		return new RecursiveIteratorIterator( $it );
	}

	public function offsetSet( $offset, $value ) {
		$data =& $this->data;
		foreach ( (array) $offset as $key ) {
			if ( !isset( $data[$key] ) ) {
				$data[$key] = array();
			}
			$data =& $data[$key];
		}
		$data = $value;
	}

	public function offsetGet( $offset ) {
		$data =& $this->data;
		foreach ( (array) $offset as $key ) {
			if ( !isset( $data[$key] ) ) {
				throw new \OutOfBoundsException( 'Does not exist' );
			} elseif ( ! is_array( $data ) ) {
				throw new \OutOfBoundsException( "Requested offset {$key} (full offset ".implode(':', $offset)."), but $data is not an array." );
			}
			$data =& $data[$key];
		}
		return $data;
	}

	public function offsetUnset( $offset ) {
		$offset = (array) $offset;
		// while loop is required to not leave behind empty arrays
		$first = true;
		while( $offset ) {
			$end = array_pop( $offset );
			$data =& $this->data;
			foreach ( $offset as $key ) {
				if ( !isset( $data[$key] ) ) {
					return;
				}
				$data =& $data[$key];
			}
			if ( $first === true || ( is_array( $data[$end] ) && !count( $data[$end] ) ) ) {
				unset( $data[$end] );
				$first = false;
			}
		}
	}

	public function offsetExists( $offset ) {
		$data =& $this->data;
		foreach ( (array) $offset as $key ) {
			if ( !isset( $data[$key] ) ) {
				return false;
			}
			$data =& $data[$key];
		}
		return true;
	}
}

// Better name?
//
// Add query arrays with a multi-dimensional position
// Merge results with their query value
// Get back result array with same positions as the origional query
//
// Maintains merge ordering
class ResultDuplicator {
	// Maps from the query array to its position in the query array
	protected $queryKeys;
	protected $queryMap;
	protected $queries = array();
	protected $result;

	public function __construct( array $queryKeys, $dimensions ) {
		$this->queryKeys = $queryKeys;
		$this->dimensions = $dimensions;
		$this->desiredOrder = new MultiDimArray;
		$this->queryMap = new MultiDimArray;
		$this->result = new MultiDimArray;
	}

	// Add a query and its position.  Positions must be unique.
	public function add( $query, $position ) {
		$dim = count( (array) $position );
		if ( $dim !== $this->dimensions ) {
			throw new \InvalidInputException( "Expection position with {$this->dimensions} dimensions, received $dim", 'invalid-input' );
		}
		$query = ObjectManager::splitFromRow( $query, $this->queryKeys );
		if ( $query === null ) {
			// the queryKeys are either unset or null, and not indexable
			// TODO: what should happen here?
			return;
		}
		$this->desiredOrder[$position] = $query;
		if ( !isset( $this->queryMap[$query] ) ) {
			$this->queries[] = $query;
			$this->queryMap[$query] = true;
		}
	}

	// merge a query into the result set
	public function merge( array $query, array $result ) {
		$query = ObjectManager::splitFromRow( $query, $this->queryKeys );
		if ( $query === null ) {
			// the queryKeys are either unset or null, and not indexable
			// TODO: what should happen here?
			return;
		}
		$this->result[$query] = $result;
	}

	public function getUniqueQueries() {
		return $this->queries;
	}

	public function getResult() {
		return self::sortResult( $this->desiredOrder->all(), $this->result, $this->dimensions );
	}

	// merge() wasn't necessarily called in the same order as add(),  this walks back through
	// the results to put them in the desired order with the correct keys.
	static public function sortResult( array $order, MultiDimArray $result, $dimensions ) {
		$final = array();
		foreach ( $order as $position => $query ) {
			if ( $dimensions > 1 ) {
				$final[$position] = self::sortResult( $query, $result, $dimensions - 1 );
			} elseif ( isset( $result[$query] ) ) {
				$final[$position] = $result[$query];
			} else {
				$final[$position] = null;
			}
		}
		return $final;
	}
}

