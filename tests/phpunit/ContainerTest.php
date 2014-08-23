<?php

namespace Flow\Tests;

use Flow\Container;

/**
 * @group Flow
 */
class ContainerTest extends FlowTestCase {

	public function testInstantiateAll() {
		$this->setMwGlobals( 'wgTitle', \Title::newMainPage() );
		$container = Container::getContainer();

		foreach ( $container->keys() as $key ) {
			$container[$key];
		}
		// All objects instantiated successfully
		$this->assertTrue( true );
	}
}
