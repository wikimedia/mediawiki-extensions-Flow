<?php

namespace Flow\Tests;

use Status;

use Flow\Container;
use Flow\Model\UUID;

class FlowTestCase extends \MediaWikiTestCase {
	protected function setUp() {
		self::useTestObjectsInContainer();

		parent::setUp();
	}

	/**
	 * Add testing versions of objects to dependency injection container
	 * This is meant to be done for all Flow tests.  However, there is no common
	 * base class since ApiTestCase does not extend from FlowTestCase.
	 *
	 * So this has to be exposed publically.
	 */
	public static function useTestObjectsInContainer() {
		// Override dependency injection container to create test environment
		$container = Container::getContainer();

		// In the test where we actually test Flow\SpamFilter\SpamBlacklist, we
		// don't use the container to get this.
		$stub = $this->getMockBuilder('Flow\\SpamFilter\\SpamBlacklist')
			->disableOriginalConstructor()
			->getMock();

		$stub->expects($this->any())
			->method( 'validate' )
			->will( $this->returnValue( Status::newGood() ) );
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
