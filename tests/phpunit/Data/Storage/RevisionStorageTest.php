<?php

namespace Flow\Tests\Data\Storage;

use Flow\Container;
use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Tests\FlowTestCase;
use MediaWiki\MainConfigNames;
use Wikimedia\Rdbms\FakeResultWrapper;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;
use Wikimedia\Rdbms\UpdateQueryBuilder;

/**
 * @covers \Flow\Data\Storage\DbStorage
 * @covers \Flow\Data\Storage\RevisionStorage
 *
 * @group Flow
 */
class RevisionStorageTest extends FlowTestCase {
	private const BEFORE_WITHOUT_CONTENT_CHANGE = [
		'rev_content_url' => 'FlowMock://location1/345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	];

	private const AFTER_WITHOUT_CONTENT_CHANGE = [
		'rev_content_url' => 'FlowMock://location1/345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'testwiki',
	];

	private const WITHOUT_CONTENT_CHANGE_DIFF = [
		'rev_mod_user_wiki' => 'testwiki',
	];

	private const BEFORE_WITH_CONTENT_CHANGE = [
		'rev_content_url' => 'FlowMock://location1/249',
		'rev_content' => 'Hello, world!<span onclick="alert(\'Hacked\');">Test</span>',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	];

	private const AFTER_WITH_CONTENT_CHANGE = [
		// URL is deliberately stale here; since the column diff shows a content
		// change, processExternalContent is in charge of updating the URL.
		'rev_content_url' => 'FlowMock://location1/249',
		'rev_content' => 'Hello, world!<span>Test</span>',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	];

	private const WITH_CONTENT_CHANGE_DIFF = [
		'rev_content' => 'FlowMock://location1/1',
		'rev_flags' => 'external',
	];

	private const MOCK_EXTERNAL_STORE_CONFIG = [
		'FlowMock://location1',
	];

	protected function setUp(): void {
		$this->overrideConfigValues( [
			MainConfigNames::ExternalStores => [ 'FlowMock' ],
			MainConfigNames::DefaultExternalStore => [ 'FlowMock://location1' ]
		] );

		\ExternalStoreFlowMock::$isUsed = false;

		parent::setUp();
	}

	public function testCalcUpdatesWithoutContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		$diff = $revStorage->calcUpdates( self::BEFORE_WITHOUT_CONTENT_CHANGE, self::AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertFalse(
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, but there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			self::WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, but there is no content change, content columns are not included in the diff'
		);
	}

	public function testCalcUpdatesWithContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		$diff = $revStorage->calcUpdates( self::BEFORE_WITH_CONTENT_CHANGE, self::AFTER_WITH_CONTENT_CHANGE );

		$this->assertTrue(
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, and there is a content change, an ExternalStoreFlowMock is constructed'
		);

		$this->assertSame(
			self::WITH_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, and there is a content change, the diff shows the updated URL'
		);
	}

	public function testCalcUpdatesWithoutContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$diff = $revStorage->calcUpdates( self::BEFORE_WITHOUT_CONTENT_CHANGE, self::AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertFalse(
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are not allowed, and there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			self::WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are not allowed, and there is no content change, content columns are not included in the diff'
		);
	}

	public function testCalcUpdatesWithContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$this->expectException( \Flow\Exception\DataModelException::class );
		$revStorage->calcUpdates( self::BEFORE_WITH_CONTENT_CHANGE, self::AFTER_WITH_CONTENT_CHANGE );
	}

	public function testUpdatingContentWhenAllowed() {
		$this->helperToTestUpdating(
			self::BEFORE_WITH_CONTENT_CHANGE,
			self::AFTER_WITH_CONTENT_CHANGE,
			self::WITH_CONTENT_CHANGE_DIFF,
			true
		);

		$this->assertTrue(
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, and there is a content change, an ExternalStoreFlowMock is constructed'
		);
	}

	public function testUpdatingContentWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );
		$this->expectException( \Flow\Exception\DataModelException::class );
		$revStorage->update(
			self::BEFORE_WITH_CONTENT_CHANGE,
			self::AFTER_WITH_CONTENT_CHANGE
		);
	}

	/**
	 * A rev ID will be added to $old and $new automatically.
	 * @param array $old
	 * @param array $new
	 * @param array $expectedUpdateValues
	 * @param bool $isContentUpdatingAllowed
	 */
	private function helperToTestUpdating( $old, $new, $expectedUpdateValues, $isContentUpdatingAllowed ) {
		$dbw = $this->createMock( IDatabase::class );
		$factory = $this->createMock( DbFactory::class );
		$factory->method( 'getDB' )
			->willReturn( $dbw );
		$id = UUID::create();

		$old['rev_id'] = $id->getBinary();
		$new['rev_id'] = $id->getBinary();

		$uqb = $this->createMock( UpdateQueryBuilder::class );
		$uqb->expects( $this->once() )->method( 'update' )
			->with( 'flow_revision' )->willReturnSelf();
		$uqb->expects( $this->once() )->method( 'set' )
			->with( $expectedUpdateValues )->willReturnSelf();
		$uqb->expects( $this->once() )->method( 'where' )
			->with( [ 'rev_id' => $id->getBinary() ] )->willReturnSelf();
		$uqb->expects( $this->once() )->method( 'caller' )->willReturnSelf();
		$dbw->expects( $this->once() )
			->method( 'newUpdateQueryBuilder' )
			->willReturn( $uqb );
		$dbw->method( 'affectedRows' )
			->willReturn( 1 );

		// Header is bare bones implementation, sufficient for testing
		// the parent class.
		$storage = new HeaderRevisionStorage(
			$factory,
			self::MOCK_EXTERNAL_STORE_CONFIG
		);

		$this->setWhetherContentUpdatingAllowed( $storage, $isContentUpdatingAllowed );
		$storage->update(
			$old,
			$new
		);
	}

	public static function isUpdatingExistingRevisionContentAllowedProvider() {
		return [
			[ true ],
			[ false ]
		];
	}

	/**
	 * @dataProvider isUpdatingExistingRevisionContentAllowedProvider
	 */
	public function testIsUpdatingExistingRevisionContentAllowed( $shouldContentUpdatesBeAllowed ) {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( $shouldContentUpdatesBeAllowed );

		$this->assertSame(
			$shouldContentUpdatesBeAllowed,
			$revStorage->isUpdatingExistingRevisionContentAllowed()
		);
	}

	/**
	 * @param bool $allowContentUpdates True to allow content updates to existing revisions
	 *
	 * @return HeaderRevisionStorage
	 */
	private function getRevisionStorageWithMockExternalStore( $allowContentUpdates ): HeaderRevisionStorage {
		$revisionStorage = new HeaderRevisionStorage(
			Container::get( 'db.factory' ),
			self::MOCK_EXTERNAL_STORE_CONFIG
		);

		$this->setWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates );
		return $revisionStorage;
	}

	private function setWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates ) {
		$klass = new \ReflectionClass( HeaderRevisionStorage::class );
		$allowedUpdateColumnsProp = $klass->getProperty( 'allowedUpdateColumns' );

		$allowedUpdateColumns = $allowedUpdateColumnsProp->getValue( $revisionStorage );

		$requiredContentColumns = [
			'rev_content',
			'rev_content_length',
			'rev_flags',
			'rev_previous_content_length',
		];

		if ( $allowContentUpdates ) {
			$allowedUpdateColumns = array_merge(
				$allowedUpdateColumns,
				$requiredContentColumns
			);

			$allowedUpdateColumns = array_unique( $allowedUpdateColumns );
		} else {
			$allowedUpdateColumns = array_diff( $allowedUpdateColumns, $requiredContentColumns );
		}

		$allowedUpdateColumnsProp->setValue( $revisionStorage, $allowedUpdateColumns );
	}

	public function testUpdateConvertsPrimaryKeyToBinary() {
		$this->helperToTestUpdating(
			[
				'rev_mod_user_id' => 0,
			],
			[
				'rev_mod_user_id' => 42,
			],
			[
				'rev_mod_user_id' => 42,
			],
			false
		);
	}

	public static function issuesQueryCountProvider() {
		return [
			[
				'Query by rev_id issues one query',
				// db queries issued
				1,
				// queries
				[
					[ 'rev_id' => 1 ],
					[ 'rev_id' => 8 ],
					[ 'rev_id' => 3 ],
				],
				// query options
				[ 'LIMIT' => 1 ]
			],

			[
				'Query by rev_id issues one query with string limit',
				// db queries issued
				1,
				// queries
				[
					[ 'rev_id' => 1 ],
					[ 'rev_id' => 8 ],
					[ 'rev_id' => 3 ],
				],
				// query options
				[ 'LIMIT' => '1' ]
			],

			[
				'Query for most recent revision issues two queries',
				// db queries issued
				2,
				// queries
				[
					[ 'rev_type_id' => 19 ],
					[ 'rev_type_id' => 22 ],
					[ 'rev_type_id' => 4 ],
					[ 'rev_type_id' => 44 ],
				],
				// query options
				[ 'LIMIT' => 1, 'ORDER BY' => [ 'rev_id DESC' ] ],
			],

		];
	}

	/**
	 * @dataProvider issuesQueryCountProvider
	 */
	public function testIssuesQueryCount( $msg, $count, array $queries, array $options ) {
		if ( !isset( $options['LIMIT'] ) || $options['LIMIT'] != 1 ) {
			$this->fail( 'Can only generate result set for LIMIT = 1' );
		}
		if ( count( $queries ) <= 2 && count( $queries ) != $count ) {
			$this->fail( '<= 2 queries always issues the same number of queries' );
		}

		$result = [];
		foreach ( $queries as $query ) {
			// this is not in any way a real result, but enough to get through
			// the result processing
			$result[] = (object)( $query + [ 'rev_id' => 42, 'tree_rev_id' => 42, 'rev_flags' => '' ] );
		}

		$factory = $this->mockDbFactory();
		// this expect is the assertion for the test
		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->method( $this->logicalOr( 'select', 'from', 'join', 'where', 'andWhere', 'groupBy', 'caller' ) )
			->willReturnSelf();
		$queryBuilder->method( 'fetchResultSet' )
			->willReturn( new FakeResultWrapper( $result ) );
		$factory->getDB( null )->expects( $this->exactly( $count ) )
			->method( 'newSelectQueryBuilder' )
			->willReturn( $queryBuilder );

		$storage = new PostRevisionStorage(
			$factory,
			false,
			$this->createMock( TreeRepository::class )
		);

		$storage->findMulti( $queries, $options );
	}

	public function testPartialResult() {
		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->method( $this->logicalOr( 'select', 'from', 'join', 'where', 'caller' ) )->willReturnSelf();
		$queryBuilder->method( 'fetchResultSet' )
			->willReturn( new FakeResultWrapper( [
				(object)[ 'rev_id' => 42, 'rev_flags' => '' ]
			] ) );
		$factory = $this->mockDbFactory();
		$factory->getDB( null )->expects( $this->once() )
			->method( 'newSelectQueryBuilder' )
			->willReturn( $queryBuilder );

		$storage = new PostRevisionStorage(
			$factory,
			false,
			$this->createMock( TreeRepository::class )
		);

		$res = $storage->findMulti(
			[
				[ 'rev_id' => 12 ],
				[ 'rev_id' => 42 ],
				[ 'rev_id' => 17 ],
			],
			[ 'LIMIT' => 1 ]
		);

		$this->assertSame(
			[
				null,
				[ [ 'rev_id' => 42, 'rev_flags' => '', 'rev_content_url' => null ] ],
				null,
			],
			$res,
			'Unfound items must be represented with null in the result array'
		);
	}

	private function mockDbFactory(): DbFactory {
		$dbw = $this->createMock( IDatabase::class );

		$factory = $this->createMock( DbFactory::class );
		$factory->method( 'getDB' )
			->willReturn( $dbw );

		return $factory;
	}
}
