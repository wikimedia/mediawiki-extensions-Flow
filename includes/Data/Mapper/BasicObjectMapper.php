<?php

namespace Flow\Data\Mapper;

use Flow\Data\ObjectMapper;

/**
 * Simplest possible implementation of ObjectMapper delgates
 * execution to closures passed in the constructor.
 *
 * This can be used to keep the mapping logic in static methods
 * within the model as so:
 *
 * $userMapper = new BasicObjectMapper(
 *     array( 'User', 'toStorageRow' ),
 *     array( 'User', 'fromStorageRow' ),
 * );
 */
class BasicObjectMapper implements ObjectMapper {
	protected $toStorageRow;

	protected $fromStorageRow;

	public function __construct( $toStorageRow, $fromStorageRow ) {
		$this->toStorageRow = $toStorageRow;
		$this->fromStorageRow = $fromStorageRow;
	}

	static public function model( $className ) {
		return new self( array( $className, 'toStorageRow' ), array( $className, 'fromStorageRow' ) );
	}

	public function toStorageRow( $object ) {
		return call_user_func( $this->toStorageRow, $object );
	}

	public function fromStorageRow( array $row, $object = null ) {
		return call_user_func( $this->fromStorageRow, $row, $object );
	}

	public function get( array $pk ) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function normalizeRow( array $row ) {
		$object = $this->fromStorageRow( $row );
		return $this->toStorageRow( $object );
	}

	public function clear() {
		// noop
	}
}
