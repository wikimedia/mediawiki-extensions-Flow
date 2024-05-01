<?php

namespace Flow\Tests\Repository;

use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Tests\FlowTestCase;
use Wikimedia\Rdbms\FakeResultWrapper;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\InsertQueryBuilder;
use Wikimedia\Rdbms\SelectQueryBuilder;

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
		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->method( $this->logicalOr( 'select', 'from', 'where', 'caller' ) )->willReturnSelf();
		$queryBuilder->method( 'fetchResultSet' )
			->willReturn( new FakeResultWrapper( [] ) );
		$db = $this->createMock( IDatabase::class );
		$db->method( $this->logicalOr( 'insert', 'insertSelect' ) )
			->willReturn( $dbResult );
		$db->method( 'addQuotes' )
			->willReturn( '' );
		$db->method( 'getSessionLagStatus' )->willReturn( [ 'lag' => 0, 'since' => 0 ] );
		$db->method( 'newInsertQueryBuilder' )->willReturnCallback( static function () use ( $db ) {
			return new InsertQueryBuilder( $db );
		} );
		$db->method( 'newSelectQueryBuilder' )
			->willReturn( $queryBuilder );
		return $db;
	}

}
