<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\FlowObjectCache;
use Flow\Model\UUID;
use WANObjectCache;

class FlowTestCase extends \MediaWikiTestCase {
	protected function setUp() {
		Container::reset();
		parent::setUp();
	}

	/**
	 * @param mixed $data
	 * @return string
	 */
	protected function dataToString( $data ) {
		foreach ( $data as $key => $value ) {
			if ( $value instanceof UUID ) {
				$data[$key] = 'UUID: ' . $value->getAlphadecimal();
			}
		}

		return parent::dataToString( $data );
	}

	protected function getCache() {
		global $wgFlowCacheTime;
		return new FlowObjectCache( WANObjectCache::newEmpty(), Container::get( 'db.factory' ), $wgFlowCacheTime );
	}
}
