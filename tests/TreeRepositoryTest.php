<?php

use Flow\Container;
use Flow\Model\UUID;
use ReflectionClass;

class TreeRepositoryTest extends \MediaWikiTestCase {

	protected $ancestor;
	protected $descendant;
	protected $cache;

	public function setUp() {
		parent::setUp();
		$this->ancestor = UUID::create( md5( time() ) );
		$this->descendant = UUID::create( md5( time() + 1000 ) );
		$this->cache = new \HashBagOStuff();
	}

	public function testInsert() {
		$treeRepository = new \Flow\Repository\TreeRepository( $this->mockDbFactory( true ), $this->cache );
		$this->assertTrue( $treeRepository->insert( $this->descendant, $this->ancestor ) );

		$reflection = new ReflectionClass( '\Flow\Repository\TreeRepository' );
		$method = $reflection->getMethod( 'cacheKey' );
		$method->setAccessible( true );

		$this->assertNotSame( $method->invoke( $treeRepository, 'subtree', $this->descendant ), false );
		$this->assertNotSame( $method->invoke( $treeRepository, 'rootpath', $this->descendant ), false );
		$this->assertNotSame( $method->invoke( $treeRepository, 'parent', $this->descendant ), false );		
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
