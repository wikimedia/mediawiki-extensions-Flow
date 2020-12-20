<?php

namespace Flow\Import\LiquidThreadsApi;

abstract class CachedApiData extends CachedData {
	/** @var ApiBackend */
	protected $backend;

	public function __construct( ApiBackend $backend ) {
		$this->backend = $backend;
	}
}
