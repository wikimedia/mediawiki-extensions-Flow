<?php

namespace Flow\Tests;

use Flow\Container;

/**
 * @covers \Flow\Container
 *
 * @group Flow
 */
class ContainerTest extends FlowTestCase {

	public function testInstantiateAll() {
		$this->setMwGlobals( 'wgTitle', \Title::newMainPage() );
		$container = Container::getContainer();

		foreach ( $container->keys() as $key ) {
			$this->assertArrayHasKey( $key, $container );
		}
	}

	public function objectManagerKeyProvider() {
		$tests = [];
		foreach ( array_unique( Container::get( 'storage.manager_list' ) ) as $key ) {
			$tests[] = [ $key ];
		}
		return $tests;
	}

	/**
	 * @dataProvider objectManagerKeyProvider
	 */
	public function testSomething( $key ) {
		$c = Container::getContainer();
		$this->assertNotNull( $c[$key] );
	}
}
