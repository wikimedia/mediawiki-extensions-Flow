<?php

namespace Flow\Data\Utils;

/**
 * Provides access to static methods of RecentChange so they
 * can be swapped out during tests
 */
class RecentChangeFactory {
	public function newFromRow( $obj ) {
		return \RecentChange::newFromRow( $obj );
	}
}
