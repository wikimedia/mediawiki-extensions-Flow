<?php

namespace Flow\Tests;

use Status;

use Flow\Container;
use Flow\Model\UUID;

class FlowTestCase extends \MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		// Override dependency injection container to create test environment
		$container = Container::getContainer();

		// In the test where we actually test Flow\SpamFilter\SpamBlacklist, we
		// don't use the container to get this.
		$stub = $this->getMockBuilder('Flow\\SpamFilter\\SpamBlacklist')
			->disableOriginalConstructor()
			->getMock();

		$stub->method( 'validate' )->willReturn( Status::newGood() );
		$container['controller.spamblacklist'] = $stub;
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
