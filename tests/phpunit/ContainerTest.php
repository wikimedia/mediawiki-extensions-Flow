<?php

namespace Flow\Tests;

use Flow\Container;
use MediaWiki\Context\RequestContext;
use MediaWiki\Title\Title;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \Flow\Container
 *
 * @group Flow
 * @group Database
 */
class ContainerTest extends FlowTestCase {

	public function testInstantiateAll() {
		RequestContext::getMain()->setTitle( Title::newMainPage() );
		$container = Container::getContainer();

		foreach ( $container->keys() as $key ) {
			$this->assertArrayHasKey( $key, $container );
		}
	}

	public static function objectManagerKeyProvider() {
		$tests = [];
		$storage = Container::get( 'storage' );
		$managerList = TestingAccessWrapper::newFromObject( $storage )->classMap;
		foreach ( array_unique( $managerList ) as $key ) {
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
