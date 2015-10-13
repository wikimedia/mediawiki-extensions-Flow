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
			->will( $this->returnValue( array( array( null, null ) ) ) );

		$this->assertEquals( array(), $locator->findMulti( array( array( 'foo' => 'random crap' ) ) ) );
	}
}
