<?php

namespace Flow\Tests;

class ObjectLocatorTest extends FlowTestCase {

	public function testUselessTest() {
		$mapper = $this->getMock( 'Flow\Data\ObjectMapper' );
		$storage = $this->getMock( 'Flow\Data\ObjectStorage' );

		$locator = new \Flow\Data\ObjectLocator( $mapper, $storage );

		$storage->expects( $this->any() )
			->method( 'findMulti' )
			->will( $this->returnValue( array( array( null, null ) ) ) );

		$this->assertEquals( array(), $locator->findMulti( array( array( 'foo' => 'random crap' ) ) ) );
	}
}
