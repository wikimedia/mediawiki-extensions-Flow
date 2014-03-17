<?php

namespace Flow\Data;

/**
 * Interface for converting back and forth between a database row and
 * a domain model.
 */
interface ObjectMapper {
	/**
	 * Convert $object from the domain model to its db row
	 *
	 * @param object $object
	 * @return array
	 */
	function toStorageRow( $object );

	/**
	 * Convert a db row to its domain model. Object passing is intended for
	 * updating the object to match a changed storage representation.
	 *
	 * @param array $row assoc array representing the domain model
	 * @param object|null $object The domain model to populate, creates when null
	 * @return object The domain model populated with $row
	 * @throws \Exception When object is the wrong class for the mapper
	 */
	function fromStorageRow( array $row, $object = null );
}
