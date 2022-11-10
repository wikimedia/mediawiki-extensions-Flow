<?php

namespace Flow\Tests\Data\Pager;

use Flow\Data\Index;
use Flow\Data\Index\TopKIndex;
use Flow\Data\ObjectManager;
use Flow\Data\Pager\Pager;
use Flow\Data\Pager\PagerPage;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;

/**
 * @covers \Flow\Data\Pager\Pager
 *
 * @group Flow
 */
class PagerTest extends \MediaWikiIntegrationTestCase {

	public static function getPageResultsProvider() {
		$objs = [];
		foreach ( range( 'A', 'J' ) as $letter ) {
			$objs[$letter] = (object)[ 'foo' => $letter ];
		}

		return [
			[
				'Gracefully returns nothing',
				// expect
				[],
				// find results
				[],
				// query options,
				[],
				// filter
				null
			],

			[
				'Returns found objects',
				// expect
				[ $objs['A'], $objs['B'] ],
				// find results
				[
					[ $objs['A'], $objs['B'] ],
				],
				// query options
				[ 'pager-limit' => 10 ],
				// filter
				null
			],

			[
				'Applies filter',
				// expect
				[ $objs['A'] ],
				// find results
				[
					[ $objs['A'], $objs['B'] ]
				],
				// query options
				[ 'pager-limit' => 10 ],
				// filter
				static function ( $found ) {
					return array_filter( $found, static function ( $obj ) {
						return $obj->foo !== 'B';
					} );
				},
			],

			[
				'Repeats query when filtered',
				// expect
				[ $objs['A'], $objs['D'] ],
				// find results
				[
					[ $objs['A'], $objs['B'], $objs['C'] ],
					[ $objs['D'], $objs['E'] ],
				],
				// query options
				[ 'pager-limit' => 2 ],
				// query filter
				static function ( $found ) {
					return array_filter( $found, static function ( $obj ) {
						return $obj->foo !== 'B' && $obj->foo !== 'C';
					} );
				},
			],

			[
				'Reverse pagination with filter',
				// expect
				[ $objs['B'], $objs['F'], $objs['I'] ],
				// find results
				[
					// note thate feature index will return these in the normal
					// forward sort order, the provided direction just means to
					// get items before rather than after the offset.
					// verified at FeatureIndexTest::testReversePagination()
					[ $objs['G'], $objs['H'], $objs['I'], $objs['J'] ],
					[ $objs['C'], $objs['D'], $objs['E'], $objs['F'] ],
					[ $objs['A'], $objs['B'] ],
				],
				// query options
				[ 'pager-limit' => 3, 'pager-dir' => 'rev', 'pager-offset' => 'K' ],
				// query filter
				static function ( $found ) {
					return array_filter( $found, static function ( $obj ) {
						return in_array( $obj->foo, [ 'I', 'F', 'B', 'A' ] );
					} );
				},
			],
		];
	}

	/**
	 * @dataProvider getPageResultsProvider
	 */
	public function testGetPageResults( $message, array $expect, array $found, array $options, $filter ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			[ 'otherthing' => 42 ],
			$options
		);
		$page = $pager->getPage( $filter );
		$this->assertInstanceOf( PagerPage::class, $page, $message );
		$this->assertEquals( $expect, $page->getResults(), $message );
	}

	public static function getPagingLinkOptionsProvider() {
		$objs = [];
		foreach ( range( 'A', 'G' ) as $letter ) {
			$objs[$letter] = (object)[ 'foo' => $letter ];
		}

		return [
			[
				'Gracefully returns nothing',
				// expect
				[],
				// find results
				[],
				// pager options
				[],
				// filter
				null
			],

			[
				'No next page with exact number of results',
				// expect
				[],
				// find results
				[
					[ $objs['A'], $objs['B'] ],
				],
				// pager options
				[ 'pager-limit' => 2 ],
				// filter
				null
			],

			[
				'Forward pagination when direction forward and extra result',
				// expect
				[
					'fwd' => [
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-B',
					],
				],
				// find results
				[
					[ $objs['A'], $objs['B'], $objs['C'] ],
				],
				// pager options
				[ 'pager-limit' => 2 ],
				// filter
				null
			],

			[
				'Forward pagination when multi-query filtered',
				// expect
				[
					'fwd' => [
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-D',
					],
				],
				// find results
				[
					[ $objs['A'], $objs['B'], $objs['C'] ],
					[ $objs['D'], $objs['E'] ],
				],
				// pager options
				[ 'pager-limit' => 2 ],
				// filter
				static function ( $found ) {
					return array_filter( $found, static function ( $obj ) {
						return $obj->foo > 'B';
					} );
				},
			],

			[
				'Multi-query edge case must issue second query',
				// expect
				[
					'fwd' => [
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-C',
					],
				],
				[
					[ $objs['A'], $objs['B'], $objs['C'] ],
					[ $objs['D'], $objs['E'], $objs['F'] ],
				],
				[ 'pager-limit' => 2 ],
				// filter
				static function ( $found ) {
					return array_filter( $found, static function ( $obj ) {
						return $obj->foo !== 'A';
					} );
				},
			],

			[
				'Reverse pagination when offset is present in options',
				// expect
				[
					'rev' => [
						'offset-dir' => 'rev',
						'limit' => 2,
						'offset' => 'serialized-B',
					],
					'fwd' => [
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-C',
					],
				],
				// find results
				[
					[ $objs['B'], $objs['C'], $objs['D'] ],
				],
				// pager options
				[
					'pager-limit' => 2,
					'pager-offset' => 'serialized-A',
					'pager-dir' => 'fwd',
				],
				// filter
				null,
			],
		];
	}

	/**
	 * @dataProvider getPagingLinkOptionsProvider
	 */
	public function testGetPagingLinkOptions( $message, array $expect, array $found, array $options, $filter ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			[ 'otherthing' => 42 ],
			$options
		);
		$page = $pager->getPage( $filter );
		$this->assertInstanceOf( PagerPage::class, $page, $message );
		$this->assertEquals( $expect, $page->getPagingLinksOptions(), $message );
	}

	public static function optionsPassedToObjectManagerFindProvider() {
		return [
			[
				'Requests one more object than pagination is for',
				// expect
				[ 'limit' => 3 ],
				// pager options
				[
					'pager-limit' => 2,
				]
			],

			[
				'Pager limit cannot be negative',
				// expect
				[ 'limit' => 2 ],
				// pager options
				[ 'pager-limit' => -99 ],
			],

			[
				'Pager limit cannot exceed 500',
				// expect
				[ 'limit' => 2 ],
				// pager options
				[ 'pager-limit' => 501 ],
			],

			[
				'Offset dir defaults to fwd',
				// expect
				[ 'offset-dir' => 'fwd' ],
				// pager options
				[],
			],

			[
				'Offset dir can be reversed',
				// expect
				[ 'offset-dir' => 'rev' ],
				// pager options
				[ 'pager-dir' => 'rev' ],
			],

			[
				'Gracefully handles unknown offset dir',
				// expect
				[ 'offset-dir' => 'fwd' ],
				// pager options
				[ 'pager-dir' => 'yabba dabba do' ],
			],

			[
				'offset-value defaults to null',
				// expect
				[ 'offset-value' => null ],
				// pager options
				[]
			],

			[
				'initial offset-value is set by providing pager-offset',
				// expect
				[ 'offset-value' => 'echo and flow' ],
				// pager options
				[ 'pager-offset' => 'echo and flow' ],
			],
		];
	}

	/**
	 * @dataProvider optionsPassedToObjectManagerFindProvider
	 */
	public function testOptionsPassedToObjectManagerFind( $message, array $expect, array $options ) {
		$om = $this->mockObjectManager();
		$om->method( 'find' )
			->with( $this->anything(), $this->callback( static function ( $opts ) use ( &$options ) {
				$options = $opts;
				return true;
			} ) );

		$pager = new Pager(
			$om,
			[ 'otherthing' => 42 ],
			$options
		);
		$page = $pager->getPage();

		$this->assertNotNull( $options );
		$optionsString = json_encode( $options );
		foreach ( $expect as $key => $value ) {
			$this->assertArrayHasKey( $key, $options, $optionsString );
			$this->assertEquals( $value, $options[$key], $optionsString );
		}
	}

	/**
	 * @param array[] $found
	 *
	 * @return ObjectManager
	 */
	private function mockObjectManager( array $found = [] ) {
		$index = $this->createMock( Index::class );
		$index->method( 'getSort' )
			->willReturn( [ 'something' ] );
		$om = $this->createMock( ObjectManager::class );
		$om->method( 'getIndexFor' )
			->willReturn( $index );
		$om->method( 'serializeOffset' )
			->willReturnCallback( static function ( $obj, $sort ) {
				return 'serialized-' . $obj->foo;
			} );

		if ( $found ) {
			$om->method( 'find' )
				->will( $this->onConsecutiveCalls(
					...array_map( [ $this, 'returnValue' ], $found )
				) );
		}

		return $om;
	}

	public function provideDataMakePagingLink() {
		return [
			[
				$this->mockStorage(
					[
						$this->createMock( TopicListEntry::class ),
						$this->createMock( TopicListEntry::class ),
						$this->createMock( TopicListEntry::class )
					],
					UUID::create(),
					[ 'topic_id' ]
				),
				[ 'topic_list_id' => '123456' ],
				[ 'pager-limit' => 2, 'order' => 'desc', 'sort' => 'topic_id' ],
				'offset-id'
			],
			[
				$this->mockStorage(
					[
						$this->createMock( TopicListEntry::class ),
						$this->createMock( TopicListEntry::class )
					],
					UUID::create(),
					[ 'workflow_last_update_timestamp' ]
				),
				[ 'topic_list_id' => '123456' ],
				[ 'pager-limit' => 1, 'order' => 'desc', 'sort' => 'workflow_last_update_timestamp', 'sortby' => 'updated' ],
				'offset'
			]
		];
	}

	/**
	 * @dataProvider provideDataMakePagingLink
	 */
	public function testMakePagingLink( ObjectManager $storage, array $query, array $options, $offsetKey ) {
		$pager = new Pager( $storage, $query, $options );
		$page = $pager->getPage();
		$pagingOption = $page->getPagingLinksOptions();
		foreach ( $pagingOption as $option ) {
			$this->assertArrayHasKey( $offsetKey, $option );
			$this->assertArrayHasKey( 'offset-dir', $option );
			$this->assertArrayHasKey( 'limit', $option );
			if ( isset( $options['sortby'] ) ) {
				$this->assertArrayHasKey( 'sortby', $option );
			}
		}
	}

	/**
	 * @param mixed $return
	 * @param mixed $offset
	 * @param string[] $sort
	 * @return ObjectManager
	 */
	private function mockStorage( $return, $offset, $sort ) {
		$storage = $this->createMock( ObjectManager::class );
		$storage->method( 'find' )
			->willReturn( $return );
		$storage->method( 'serializeOffset' )
			->willReturn( $offset );
		$storage->method( 'getIndexFor' )
			->willReturn( $this->mockIndex( $sort ) );
		return $storage;
	}

	/**
	 * @param string[] $sort
	 * @return TopKIndex
	 */
	private function mockIndex( $sort ) {
		$index = $this->createMock( TopKIndex::class );
		$index->method( 'getSort' )
			->willReturn( $sort );
		return $index;
	}
}
