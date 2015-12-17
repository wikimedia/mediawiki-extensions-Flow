<?php

namespace Flow\Data\Index;

use BagOStuff;
use Flow\Data\BufferedCache;
use Flow\Data\ObjectManager;
use Flow\Data\ObjectMapper;
use Flow\Data\ObjectStorage;
use Flow\Data\Compactor\ShallowCompactor;
use Flow\Data\Utils\SortArrayByKeys;
use Flow\Exception\DataModelException;
use Flow\Exception\InvalidInputException;

/**
 * Holds the top k items with matching $indexed columns.  List is sorted and truncated to specified size.
 */
class TopKIndex extends FeatureIndex {
	/**
	 * @var array
	 */
	protected $options = array();

	public function __construct( BufferedCache $cache, ObjectStorage $storage, ObjectMapper $mapper, $prefix, array $indexed, array $options = array() ) {
		if ( empty( $options['sort'] ) ) {
			throw new InvalidInputException( 'TopKIndex must be sorted', 'invalid-input' );
		}

		parent::__construct( $cache, $storage, $mapper, $prefix, $indexed );

		$this->options = $options + array(
			'limit' => 500,
			'order' => 'DESC',
			'create' => function() { return false; },
			'shallow' => null,
		);
		$this->options['order'] = strtoupper( $this->options['order'] );

		if ( !is_array( $this->options['sort'] ) ) {
			$this->options['sort'] = array( $this->options['sort'] );
		}
		if ( $this->options['shallow'] ) {
			// TODO: perhaps we shouldn't even get a shallow option, just receive a proper compactor in FeatureIndex::__construct
			$this->rowCompactor = new ShallowCompactor( $this->rowCompactor, $this->options['shallow'], $this->options['sort'] );
		}
	}

	public function canAnswer( array $keys, array $options ) {
		if ( !parent::canAnswer( $keys, $options ) ) {
			return false;
		}

		if ( isset( $options['offset-id'] ) || ( isset( $options['offset-dir'] ) && $options['offset-dir'] !== 'fwd' ) ) {
			return false;
		}

		if ( isset( $options['sort'], $options['order'] ) ) {
			return ObjectManager::makeArray( $options['sort'] ) === $this->options['sort']
				&& strtoupper( $options['order'] ) === $this->options['order'];
		}
		return true;
	}

	public function getLimit() {
		return $this->options['limit'];
	}

	protected function filterResults( array $results, array $options = array() ) {
		foreach ( $results as $i => $result ) {
			list( $offset, $limit ) = $this->getOffsetLimit( $result, $options );
			$results[$i] = array_slice( $result, $offset, $limit, true );
		}

		return $results;
	}

	// TODO: This is only left for now to handle non-ID offsets (e.g. updated
	// timestamps).
	//
	// This has always been broken once you query past the TopKIndex limit.
	/**
	 * @param array $rows
	 * @param array $options
	 * @return array [offset, limit] 0-based index to start with and limit.
	 */
	protected function getOffsetLimit( $rows, $options ) {
		$limit = isset( $options['limit'] ) ? $options['limit'] : $this->getLimit();

		$offsetValue = isset( $options['offset-value'] ) ? $options['offset-value'] : null;

		$dir = 'fwd';
		if (
			isset( $options['offset-dir'] ) &&
			$options['offset-dir'] === 'rev'
		) {
			$dir = 'rev';
		}

		if ( $offsetValue === null ) {
			$offset = $dir === 'fwd' ? 0 : count( $rows ) - $limit;
			return array( $offset, $limit );
		}

		$offset = $this->getOffsetFromOffsetValue( $rows, $offsetValue );
		$includeOffset = isset( $options['include-offset'] ) && $options['include-offset'];
		if ( $dir === 'fwd' ) {
			if ( $includeOffset ) {
				$startPos = $offset;
			} else {
				$startPos = $offset + 1;
			}
		} elseif ( $dir === 'rev' ) {
			$startPos = $offset - $limit;
			if ( $includeOffset ) {
				$startPos++;
			}

			if ( $startPos < 0 ) {
				if (
					isset( $options['offset-elastic'] ) &&
					$options['offset-elastic'] === false
				) {
					// If non-elastic, then reduce the number of items shown commensurately
					$limit += $startPos;
				}
				$startPos = 0;
			}
		} else {
			$startPos = 0;
		}

		return array( $startPos, $limit );
	}

	/**
	 * Returns the 0-indexed position of $offsetValue within $rows or throws a
	 * DataModelException if $offsetValue is not contained within $rows
	 *
	 * @todo seems wasteful to pass string offsetValue instead of exploding when it comes in
	 * @param array $rows Current bucket contents
	 * @param string $offsetValue
	 * @return int The position of $offsetValue within $rows
	 * @throws DataModelException When $offsetValue is not found within $rows
	 */
	protected function getOffsetFromOffsetValue( $rows, $offsetValue ) {
		$rowIndex = 0;
		$nextInOrder = $this->getOrder() === 'DESC' ? -1 : 1;
		foreach ( $rows as $row ) {
			$comparisonValue = $this->compareRowToOffsetValue( $row, $offsetValue );
			if ( $comparisonValue === 0 || $comparisonValue === $nextInOrder ) {
				return $rowIndex;
			}
			$rowIndex++;
		}

		throw new DataModelException( 'Unable to find specified offset in query results', 'process-data' );
	}

	/**
	 * @param array $row Row to compare to
	 * @param string $offsetValue Value to compare to.  For instance, a timestamp if we
	 *  want all rows before/after that timestamp.  This consists of values for each field
	 *  we sort by, delimited by |.
	 *
	 * @return integer An integer less than, equal to, or greater than zero
	 *  if $row is considered to be respectively less than, equal to, or
	 *  greater than $offsetValue
	 *
	 * @throws DataModelException When the index does not support offset values due to
	 *  having an undefined sort order.
	 */
	public function compareRowToOffsetValue( array $row, $offsetValue ) {
		$sortFields = $this->getSort();
		$splitOffsetValue = explode( '|', $offsetValue );
		$fieldIndex = 0;

		if ( $sortFields === false ) {
			throw new DataModelException( 'This Index implementation does not support offset values', 'process-data' );
		}

		foreach( $sortFields as $field ) {
			$valueInRow = $row[$field];
			$offsetValuePart = $splitOffsetValue[$fieldIndex];

			if ( $valueInRow > $offsetValuePart ) {
				return 1;
			} elseif ( $valueInRow < $offsetValuePart ) {
				return -1;
			}
			++$fieldIndex;
		}

		return 0;
	}

	protected function maybeCreateIndex( array $indexed, array $sourceRow, array $compacted ) {
		if ( call_user_func( $this->options['create'], $sourceRow ) ) {
			$this->cache->set( $this->cacheKey( $indexed ), array( $compacted ) );
			return true;
		}
		return false;
	}

	protected function addToIndex( array $indexed, array $row ) {
		$self = $this;

		// If this used redis instead of memcached, could it add to index in position
		// without retry possibility? need a single number that will properly sort rows.
		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $self, $row ) {
				if ( $value === false ) {
					return false;
				}

				if ( count( $value ) > 0 ) {
					/*
					 * Ideally, we'd expand the rows currently in cache, run them
					 * through $this->storage->normalize & then re-compact them.
					 * And then we can reliably locate $row in there.
					 * However, that may require additional cache lookups for
					 * the expand info.
					 *
					 * Instead of doing that, Let's just make the current row
					 * columns conform the rows in cache ($schema)
					 *
					 * This is mostly to fight useless nullable columns in DB
					 * (either in preparation for schema change or no longer needed)
					 * Meaningful changes in data will need a cache key change, so
					 * we're good here.
					 */
					$row = $self->normalizeCompressed( $row, array_keys( reset( $value ) ) );
				}

				$idx = array_search( $row, $value );
				if ( $idx !== false ) {
					return false; // This row already exists somehow
				}
				$retval = $value;
				$retval[] = $row;
				$retval = $self->sortIndex( $retval );
				$retval = $self->limitIndexSize( $retval );
				if ( $retval === $value ) {
					// object didn't fit in index
					return false;
				} else {
					return $retval;
				}
			}
		);
	}

	protected function removeFromIndex( array $indexed, array $row ) {
		$self = $this;

		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $self, $row ) {
				if ( $value === false ) {
					return false;
				}

				if ( count( $value ) > 0 ) {
					// see comment in self::addToIndex on why to normalize
					$row = $self->normalizeCompressed( $row, array_keys( reset( $value ) ) );
				}

				$idx = array_search( $row, $value );
				if ( $idx === false ) {
					return false;
				}
				unset( $value[$idx] );
				return $value;
			}
		);
	}

	protected function replaceInIndex( array $indexed, array $oldRow, array $newRow ) {
		$self = $this;
		$this->cache->merge(
			$this->cacheKey( $indexed ),
			function( BagOStuff $cache, $key, $value ) use( $self, $oldRow, $newRow ) {
				if ( $value === false ) {
					return false;
				}
				$retval = $value;

				if ( count( $value ) > 0 ) {
					// see comment in self::addToIndex on why to normalize
					$oldRow = $self->normalizeCompressed( $oldRow, array_keys( reset( $value ) ) );
					$newRow = $self->normalizeCompressed( $newRow, array_keys( reset( $value ) ) );
				}

				$idx = array_search( $oldRow, $retval );
				if ( $idx !== false ) {
					unset( $retval[$idx] );
				}
				$retval[] = $newRow;
				$retval = $self->sortIndex( $retval );
				$retval = $self->limitIndexSize( $retval );
				if ( $value === $retval ) {
					// new item didn't fit in index and old item wasn't found in index
					return false;
				} else {
					return $retval;
				}
			}
		);
	}

	/**
	 * In order to be able to reliably find a row in an array of
	 * cached rows, we need to normalize those to make sure the
	 * columns match: they may be outdated.
	 *
	 * @param array $row Array in [column => value] format
	 * @param array $schema Array of column names to be present in $row
	 * @return array
	 */
	// INTERNAL: in 5.4 it can be protected
	public function normalizeCompressed( array $row, array $schema ) {
		$schema = array_fill_keys( $schema, null );

		// add null value for columns currently in cache
		$row = array_merge( $schema, $row );

		// remove unknown columns from the row
		$row = array_intersect_key( $row, $schema );

		return $row;
	}

	// INTERNAL: in 5.4 it can be protected
	public function sortIndex( array $values ) {
		// I don't think this is a valid way to sort a 128bit integer string
		$callback = new SortArrayByKeys( $this->options['sort'], true );
		/** @noinspection PhpParamsInspection */
		usort( $values, $callback );
		if ( $this->options['order'] === 'DESC' ) {
			$values = array_reverse( $values );
		}
		return $values;
	}

	// INTERNAL: in 5.4 it can be protected
	public function limitIndexSize( array $values ) {
		return array_slice( $values, 0, $this->options['limit'] );
	}

	// INTERNAL: in 5.4 it can be protected
	public function queryOptions() {
		$options = array( 'LIMIT' => $this->options['limit'] );

		$orderBy = array();
		$order = $this->options['order'];
		foreach ( $this->options['sort'] as $key ) {
			$orderBy[] = "$key $order";
		}
		$options['ORDER BY'] = $orderBy;

		return $options;
	}
}
