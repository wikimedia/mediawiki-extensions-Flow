<?php

namespace Flow\Collection;

/**
 * This class represents an array of AbstractRevision objects. It is accessible
 * in most ways an array is, but does not initially hold the AbstractRevision
 * objects. Those are only resolved if and once the data is actually needed.
 */
class RevisionArrayStub extends \ArrayIterator {
	/**
	 * @var DelayedAbstractCollection|null
	 */
	protected $collection;

	/**
	 * @var \ArrayIterator|null
	 */
	protected $iterator;

	/**
	 * Initializes the array-like object with the collection, which will be
	 * required to resolve the data once it's needed.
	 *
	 * @param DelayedAbstractCollection $collection
	 */
	public function init( DelayedAbstractCollection $collection ) {
		$this->collection = $collection;
	}

	/**
	 * Whenever we try to fetch data from this "array", this method will be
	 * called. This will resolve the queries in the collection object and
	 * build the real ArrayIterator around the real revisions.
	 */
	protected function getIterator() {
		if ( !$this->iterator ) {
			$this->collection->resolveQueries();

			$revisions = $this->collection->getLoadedRevisions();
			return $this->iterator = new \ArrayIterator( $revisions );
		}

		return $this->iterator;
	}

	public function append( $value ) {
		$this->getIterator()->append( $value );
	}

	public function asort() {
		$this->getIterator()->asort();
	}

	public function count() {
		return $this->getIterator()->count();
	}

	public function current() {
		return $this->getIterator()->current();
	}

	public function getArrayCopy() {
		return $this->getIterator()->getArrayCopy();
	}

	public function key() {
		return $this->getIterator()->key();
	}

	public function ksort() {
		$this->getIterator()->ksort();
	}

	public function natcasesort() {
		$this->getIterator()->natcasesort();
	}

	public function natsort() {
		$this->getIterator()->natsort();
	}

	public function next() {
		$this->getIterator()->next();
	}

	public function offsetExists( $index ) {
		return $this->getIterator()->offsetExists( $index );
	}

	public function offsetGet( $index ) {
		return $this->getIterator()->offsetGet( $index );
	}

	public function offsetSet( $index, $newval ) {
		$this->getIterator()->offsetSet( $index, $newval );
	}

	public function offsetUnset( $index ) {
		$this->getIterator()->offsetUnset( $index );
	}

	public function rewind() {
		$this->getIterator()->rewind();
	}

	public function seek( $position ) {
		$this->getIterator()->seek( $position );
	}

	public function uasort( $cmp_function ) {
		$this->getIterator()->uasort( $cmp_function );
	}

	public function uksort( $cmp_function ) {
		$this->getIterator()->uksort( $cmp_function );
	}

	public function valid() {
		return $this->getIterator()->valid();
	}
}
