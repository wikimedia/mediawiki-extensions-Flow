<?php

namespace Flow\Data\Index;

/**
 * Offers direct lookup of an object via a unique feature(set of properties)
 * on the object.
 */
class UniqueFeatureIndex extends FeatureIndex {

	public function getLimit() {
		return 1;
	}

	public function queryOptions() {
		return [ 'LIMIT' => $this->getLimit() ];
	}

	protected function removeFromIndex( array $indexed, array $row ) {
		$this->cache->delete( $this->cacheKey( $indexed ) );
	}
}
