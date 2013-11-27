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
		global $wgFlowCluster;

		if ( $wgFlowCluster ) {
			$lb = wfGetLBFactory()->getExternalLB( $wgFlowCluster, $this->wiki );
		} else {
			$lb = wfGetLB( $this->wiki );
		}

		return $lb->getConnection( $db, $groups, $this->wiki );
	}

	public function getLB() {
		return wfGetLB( $this->wiki );
	}
}
