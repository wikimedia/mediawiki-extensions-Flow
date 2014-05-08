<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\UUID;
use ReflectionClass;

/**
 * @group Flow
 */
class TreeRepositoryTest extends FlowTestCase {

	protected $ancestor;
	protected $descendant;

	public function setUp() {
		parent::setUp();
		$this->ancestor = UUID::create( md5( time() ) );
		$this->descendant = UUID::create( md5( time() + 1000 ) );
	}

	public function testSuccessfulInsert() {
		$cache = new \HashBagOStuff();
		$treeRepository = new \Flow\Repository\TreeRepository( $this->mockDbFactory( true ), $cache );
		$this->assertTrue( $treeRepository->insert( $this->descendant, $this->ancestor ) );

		$reflection = new ReflectionClass( '\Flow\Repository\TreeRepository' );
		$method = $reflection->getMethod( 'cacheKey' );
		$method->setAccessible( true );

		$this->assertNotSame( $cache->get( $method->invoke( $treeRepository, 'subtree', $this->descendant ) ), false );
		$this->assertNotSame( $cache->get( $method->invoke( $treeRepository, 'rootpath', $this->descendant ) ), false );
		$this->assertNotSame( $cache->get( $method->invoke( $treeRepository, 'parent', $this->descendant ) ), false );
	}

	/**
	 * @expectedException \Flow\Exception\DataModelException
	 */
	public function testFailingInsert() {
		// Catch the exception and test the cache result then re-throw the exception,
		// otherwise the exception would skip the cache result test
		$cache = new \HashBagOStuff();
		try {
			$treeRepository = new \Flow\Repository\TreeRepository( $this->mockDbFactory( false ), $cache );
			$this->assertNull( $treeRepository->insert( $this->descendant, $this->ancestor ) );
		} catch ( \Exception $e ) {
			$reflection = new ReflectionClass( '\Flow\Repository\TreeRepository' );
			$method = $reflection->getMethod( 'cacheKey' );
			$method->setAccessible( true );

			$this->assertSame( $cache->get( $method->invoke( $treeRepository, 'rootpath', $this->descendant ) ), false );
			$this->assertSame( $cache->get( $method->invoke( $treeRepository, 'parent', $this->descendant ) ), false );

			throw $e;
		}
	}

	protected function mockDbFactory( $dbResult ) {
		$dbFactory = $this->getMockBuilder( '\Flow\DbFactory' )
			->disableOriginalConstructor()
			->getMock();
		$dbFactory->expects( $this->any() )
			->method( 'getDB' )
			->will( $this->returnValue( $this->mockDb( $dbResult) ) );
		return $dbFactory;
	}

	protected function mockDb( $dbResult ) {
		$db = $this->getMockBuilder( '\DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();
		$db->expects( $this->any() )
			->method( 'insert' )
			->will( $this->returnValue( $dbResult ) );
		$db->expects( $this->any() )
			->method( 'insertSelect' )
			->will( $this->returnValue( $dbResult ) );
		$db->expects( $this->any() )
			->method( 'addQuotes' )
			->will( $this->returnValue( '' ) );
		return $db;
	}

}
