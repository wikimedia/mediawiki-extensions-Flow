<?php

namespace Flow\Tests\Repository;

use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Tests\FlowTestCase;
use Wikimedia\Rdbms\IDatabase;

/**
 * @covers \Flow\Repository\TreeRepository
 *
 * @group Flow
 */
class TreeRepositoryTest extends FlowTestCase {

	/** @var UUID */
	private $ancestor;
	/** @var UUID */
	private $descendant;

	protected function setUp(): void {
		parent::setUp();
		$this->ancestor = UUID::create();
		$this->descendant = UUID::create();
	}

	public function testSuccessfulInsert() {
		$dbFactory = $this->mockDbFactory( true );
		$cache = $this->getCache( $dbFactory );
		$treeRepository = new TreeRepository( $dbFactory, $cache );
		$this->assertTrue( $treeRepository->insert( $this->descendant, $this->ancestor ) );
	}

	private function mockDbFactory( $dbResult ) {
		$dbFactory = $this->createMock( DbFactory::class );
		$dbFactory->method( 'getDB' )
			->willReturn( $this->mockDb( $dbResult ) );
		return $dbFactory;
	}

	private function mockDb( $dbResult ) {
		$db = $this->createMock( IDatabase::class );
		$db->method( $this->logicalOr( 'insert', 'insertSelect' ) )
			->willReturn( $dbResult );
		$db->method( 'addQuotes' )
			->willReturn( '' );
		$db->method( 'getSessionLagStatus' )->willReturn( [ 'lag' => 0, 'since' => 0 ] );
		return $db;
	}

}
