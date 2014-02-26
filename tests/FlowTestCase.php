<?php

namespace Flow\Tests;

use Flow\Model\UUID;

class FlowTestCase extends \MediaWikiTestCase {

	protected function setUp() {
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
}
