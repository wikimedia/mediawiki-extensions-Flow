<?php

namespace Flow;

class ContainerTest extends \MediaWikiTestCase {

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
