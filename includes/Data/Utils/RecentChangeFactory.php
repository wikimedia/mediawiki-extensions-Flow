<?php

namespace Flow\Data\Utils;

use MediaWiki\RecentChanges\RecentChange;
use stdClass;

/**
 * Provides access to static methods of RecentChange so they
 * can be swapped out during tests
 */
class RecentChangeFactory {

	public function newFromRow( stdClass $obj ): RecentChange {
		$rc = RecentChange::newFromRow( $obj );
		// status key is always "changed" for now.
		$rc->setExtra( [ 'pageStatus' => 'changed' ] );
		return $rc;
	}
}
