<?php

namespace Flow\Collection;

use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;

/**
 * This class represents an AbstractRevision object. It is accessible in any way
 * the actual AbstractRevision object is, but does not initially hold the
 * AbstractRevision object. That is only resolved if and once the data is
 * actually needed.
 */
class RevisionStub {
	/**
	 * @var \Flow\Model\AbstractRevision
	 */
	protected $object;

	/**
	 * @var DelayedAbstractCollection
	 */
	protected $collection;

	/**
	 * @var \Flow\Model\UUID|\Closure
	 */
	protected $revisionId;

	/**
	 * Construct a delayed object that servers as a pass-through to an
	 * AbstractRevision object.
	 * $revisionId can be either a UUID, or a Closure that will be called with
	 * the collection as only parameter (for when the UUID is not known at the
	 * time this object is created)
	 *
	 * @param AbstractCollection $collection
	 * @param UUID|\Closure $revisionId
	 */
	public function __construct( DelayedAbstractCollection $collection, $revisionId ) {
		$this->collection = $collection;
		$this->revisionId = $revisionId;
	}

	/**
	 * @return UUID
	 * @throws \Flow\Exception\InvalidDataException
	 */
	public function getRevisionId() {
		if ( $this->revisionId instanceof UUID ) {
			return $this->revisionId;
		} elseif ( $this->revisionId instanceof \Closure ) {
			$this->collection->resolveQueries();
			return $this->revisionId = call_user_func( $this->revisionId, $this->collection );
		}

		throw new InvalidDataException( 'Revision id could not be found', 'fail-load-data' );
	}

	/**
	 * @return \Flow\Model\AbstractRevision
	 */
	protected function getObject() {
		if ( !$this->object ) {
			$this->collection->resolveQueries();
			$this->object = $this->collection->getRevisionObject( $this->getRevisionId() );
		}

		return $this->object;
	}

	// a lot a magic methods, just passing stuff on to the real revision object

	public function __call( $name, $arguments ) {
		return call_user_func_array( array( $this->getObject(), $name ), $arguments );
	}

	public function __get( $name ) {
		return $this->getObject()->$name;
	}

	public function __set( $name, $value) {
		$this->getObject()->$name = $value;
	}

	public function __isset( $name ) {
		return isset( $this->getObject()->$name );
	}

	public function __unset( $name ) {
		unset( $this->getObject()->$name );
	}

	public function __sleep() {
		$this->object = serialize( $this->getObject() );
		return array( 'object' );
	}

	public function __wakeup() {
		$this->object = unserialize( $this->object );
	}

	public function __toString() {
		return $this->getObject()->__toString();
	}

	public function __invoke( $args = null ) {
		return call_user_func_array( array( $this->getObject() ), func_get_args() );
	}

	public function __set_state( $properties ) {
		return $this->getObject();
	}

	public function __clone() {
		return clone $this->getObject();
	}
}
