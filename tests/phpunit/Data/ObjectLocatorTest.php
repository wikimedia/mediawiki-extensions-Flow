<?php

namespace Flow\Tests\Data;

use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class ObjectLocatorTest extends FlowTestCase {

	public function testUselessTest() {
		$mapper = $this->getMock( 'Flow\Data\ObjectMapper' );
		$storage = $this->getMock( 'Flow\Data\ObjectStorage' );
		$dbFactory = $this->getMock( 'Flow\DbFactory' );

		$locator = new \Flow\Data\ObjectLocator( $mapper, $storage, $dbFactory );

		$storage->expects( $this->any() )
			->method( 'findMulti' )
			->will( $this->returnValue( [ [ null, null ] ] ) );

		$this->assertEquals( [], $locator->findMulti( [ [ 'foo' => 'random crap' ] ] ) );
	}
}
