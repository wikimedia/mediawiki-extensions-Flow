<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Iterator;

abstract class CachedApiData extends CachedData {
	protected $backend;

	function __construct( ApiBackend $backend ) {
		$this->backend = $backend;
	}
}
