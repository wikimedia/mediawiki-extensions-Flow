<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Iterator;

/**
 * Cached MediaWiki page data.
 */
class CachedPageData extends CachedApiData {
	protected function retrieve( array $ids ) {
		return $this->backend->retrievePageDataByID( $ids );
	}
}
