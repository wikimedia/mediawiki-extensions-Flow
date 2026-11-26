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
 *     [ 'User', 'toStorageRow' ],
 *     [ 'User', 'fromStorageRow' ],
 * );
 */
class BasicObjectMapper implements ObjectMapper {
	/** @var callable */
	protected $toStorageRow;

	/** @var callable */
	protected $fromStorageRow;

	/**
	 * @param callable $toStorageRow
	 * @param callable $fromStorageRow
	 */
	public function __construct( $toStorageRow, $fromStorageRow ) {
		$this->toStorageRow = $toStorageRow;
		$this->fromStorageRow = $fromStorageRow;
	}

	/**
	 * @param class-string $className
	 * @return self
	 */
	public static function model( $className ) {
		return new self( $className::toStorageRow( ... ), $className::fromStorageRow( ... ) );
	}

	public function toStorageRow( $object ) {
		return ( $this->toStorageRow )( $object );
	}

	public function fromStorageRow( array $row, $object = null ) {
		return ( $this->fromStorageRow )( $row, $object );
	}

	public function get( array $pk ) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function normalizeRow( array $row ) {
		$object = $this->fromStorageRow( $row );
		return $this->toStorageRow( $object );
	}

	public function clear() {
		// noop
	}
}
