<?php

namespace Flow\Tests\Data\Index;

use Flow\Data\Index\FeatureIndex;

/**
 * @group Flow
 */
class MockFeatureIndex extends FeatureIndex {
	public function getLimit() { return 42; }
	public function queryOptions() { return array(); }
	public function limitIndexSize( array $values ) { return $values; }
	public function addToIndex( array $indexed, array $row ) {}
	public function removeFromIndex( array $indexed, array $row ) {}

	// not abstract, but override for convenience
	public function getSort() { return array( 'some_row' ); }
	public function getOrder() { return 'ASC'; }
}

