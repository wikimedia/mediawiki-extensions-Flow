<?php

namespace Flow\Tests\Repository;

use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Tests\FlowTestCase;
use Wikimedia\Rdbms\DatabaseMysqli;

/**
 * @group Flow
 */
class TreeRepositoryTest extends FlowTestCase {

	protected $ancestor;
	protected $descendant;

	protected function setUp() {
		parent::setUp();
		$this->ancestor = UUID::create( false );
		$this->descendant = UUID::create( false );
	}

	public function testSuccessfulInsert() {
		$cache = $this->getCache();
		$treeRepository = new TreeRepository( $this->mockDbFactory( true ), $cache );
		$this->assertTrue( $treeRepository->insert( $this->descendant, $this->ancestor ) );
	}

	/**
	 * @expectedException \Flow\Exception\DataModelException
	 */
	public function testFailingInsert() {
		$treeRepository = new TreeRepository( $this->mockDbFactory( false ), $this->getCache() );
		$treeRepository->insert( $this->descendant, $this->ancestor );
	}

	protected function mockDbFactory( $dbResult ) {
		$dbFactory = $this->getMockBuilder( '\Flow\DbFactory' )
			->disableOriginalConstructor()
			->getMock();
		$dbFactory->expects( $this->any() )
			->method( 'getDB' )
			->will( $this->returnValue( $this->mockDb( $dbResult ) ) );
		return $dbFactory;
	}

	protected function mockDb( $dbResult ) {
		$db = $this->getMockBuilder( DatabaseMysqli::class )
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
