<?php

namespace Flow\Serializer;

/**
 * Mediocre priority queue implementation, we don't need anything better
 * since we only need to get the fully ordered result once per queue. If
 * profiling shows any need this can be more performant, but more complicated.
 *
 * This PriorityQueue guarantees that if two values are added with the same priority
 * the first one added will be the first one returned.
 */
class PriorityQueue {
	/**
	 * @param mixed $value
	 * @param integer $priority Higher numbers are returned first
	 */
	public function insert( $value, $priority ) {
		$this->data[$priority][] = $value;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		ksort( $this->data );
		return call_user_func_array( 'array_merge', $this->data );
	}
}
