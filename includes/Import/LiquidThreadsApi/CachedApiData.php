<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Iterator;

/**
 * Abstract class to store ID-indexed cached data.
 */
abstract class CachedApiData extends CachedData {
	protected $backend;

	function __construct( ApiBackend $backend ) {
		$this->backend = $backend;
	}
}

