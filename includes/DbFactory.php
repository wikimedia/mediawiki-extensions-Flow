<?php

namespace Flow;

/**
 * Super simple class,  provide the name of the "wiki" used for flow
 * data.  All classes within flow that need to access the db will go through
 * here
 */
class DbFactory {
	public function __construct( $wiki = false ) {
		$this->wiki = $wiki;
	}

	public function getDB( $db, $groups = array() ) {
		return wfGetDB( $db, $groups, $this->wiki );
	}

	public function getLB() {
		return wfGetLB( $this->wiki );
	}
}
