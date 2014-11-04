<?php

namespace Flow\Tests\Data\Pager;

use Flow\Data\BagOStuff;
use Flow\Data\BagOStuff\LocalBufferedBagOStuff;
use Flow\Data\BufferedCache;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Pager\Pager;
use stdClass;

/**
 * @group Flow
 */
class PagerTest extends \MediaWikiTestCase {

	public static function getPageResultsProvider() {
		$objs = array();
		foreach ( range( 'A', 'J' ) as $letter ) {
			$objs[$letter] = (object)array( 'foo' => $letter );
		}

		return array(
			array(
				'Gracefully returns nothing',
				// expect
				array(),
				// find results
				array(),
				// query options,
				array(),
				// filter
				null
			),

			array(
				'Returns found objects',
				// expect
				array( $objs['A'], $objs['B'] ),
				// find results
				array(
					array( $objs['A'], $objs['B'] ),
				),
				// query options
				array( 'pager-limit' => 10 ),
				// filter
				null
			),

			array(
				'Applies filter',
				// expect
				array( $objs['A'] ),
				// find results
				array(
					array( $objs['A'], $objs['B'] )
				),
				// query options
				array( 'pager-limit' => 10 ),
				// filter
				function( $found ) {
					return array_filter( $found, function( $obj ) { return $obj->foo !== 'B'; } );
				},
			),

			array(
				'Repeats query when filtered',
				// expect
				array( $objs['A'], $objs['D'] ),
				// find results
				array(
					 array( $objs['A'], $objs['B'], $objs['C'] ),
					 array( $objs['D'], $objs['E'] ),
				),
				// query options
				array( 'pager-limit' => 2 ),
				// query filter
				function( $found ) {
					return array_filter( $found, function( $obj ) {
						return $obj->foo !== 'B' && $obj->foo !== 'C';
					} );
				},
			),

			array(
				'Reverse pagination with filter',
				// expect
				array( $objs['B'], $objs['F'], $objs['I'] ),
				// find results
				array(
					// note thate feature index will return these in the normal
					// forward sort order, the provided direction just means to
					// get items before rather than after the offset.
					// verified at FeatureIndexTest::testReversePagination()
					array( $objs['G'], $objs['H'], $objs['I'], $objs['J'] ),
					array( $objs['C'], $objs['D'], $objs['E'], $objs['F'] ),
					array( $objs['A'], $objs['B'] ),
				),
				// query options
				array( 'pager-limit' => 3, 'pager-dir' => 'rev', 'pager-offset' => 'K' ),
				// query filter
				function( $found ) {
					return array_filter( $found, function( $obj ) {
						return in_array( $obj->foo, array( 'I', 'F', 'B', 'A' ) );
					} );
				},
			),
		);
	}

	/**
	 * @dataProvider getPageResultsProvider
	 */
	public function testGetPageResults( $message, $expect, $found, array $options, $filter ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			array( 'otherthing' => 42 ),
			$options
		);
		$page = $pager->getPage( $filter );
		$this->assertInstanceOf( 'Flow\Data\Pager\PagerPage', $page, $message );
		$this->assertEquals( $expect, $page->getResults(), $message );
	}


	public static function getPagingLinkOptionsProvider() {
		$objs = array();
		foreach ( range( 'A', 'G' ) as $letter ) {
			$objs[$letter] = (object)array( 'foo' => $letter );
		}

		return array(
			array(
				'Gracefully returns nothing',
				// expect
				array(),
				// find results
				array(),
				// pager options
				array(),
				// filter
				null
			),

			array(
				'No next page with exact number of results',
				// expect
				array(),
				// find results
				array(
					array( $objs['A'], $objs['B'] ),
				),
				// pager options
				array( 'pager-limit' => 2 ),
				// filter
				null
			),

			array(
				'Forward pagination when direction forward and extra result',
				// expect
				array(
					'fwd' => array(
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-B',
					),
				),
				// find results
				array(
					array( $objs['A'], $objs['B'], $objs['C'] ),
				),
				// pager options
				array( 'pager-limit' => 2 ),
				// filter
				null
			),

			array(
				'Forward pagination when multi-query filtered',
				// expect
				array(
					'fwd' => array(
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-D',
					),
				),
				// find results
				array(
					array( $objs['A'], $objs['B'], $objs['C'] ),
					array( $objs['D'], $objs['E'] ),
				),
				// pager options
				array( 'pager-limit' => 2 ),
				// filter
				function( $found ) {
					return array_filter( $found, function( $obj ) { return $obj->foo > 'B'; } );
				},
			),

			array(
				'Multi-query edge case must issue second query',
				// expect
				array(
					'fwd' => array(
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-C',
					),
				),
				array(
					array( $objs['A'], $objs['B'], $objs['C'] ),
					array( $objs['D'], $objs['E'], $objs['F'] ),
				),
				array( 'pager-limit' => 2 ),
				// filter
				function( $found ) {
					return array_filter( $found, function( $obj ) { return $obj->foo !== 'A'; } );
				},
			),

			array(
				'Reverse pagination when offset-id is present in options',
				// expect
				array(
					'rev' => array(
						'offset-dir' => 'rev',
						'limit' => 2,
						'offset' => 'serialized-B',
					),
					'fwd' => array(
						'offset-dir' => 'fwd',
						'limit' => 2,
						'offset' => 'serialized-C',
					),
				),
				// find results
				array(
					array( $objs['B'], $objs['C'], $objs['D'] ),
				),
				// pager options
				array(
					'pager-limit' => 2,
					'pager-offset' => 'serialized-A',
					'pager-dir' => 'fwd',
				),
				// filter
				null,
			),
		);
	}

	/**
	 * @dataProvider getPagingLinkOptionsProvider
	 */
	public function testGetPagingLinkOptions( $message, $expect, $found, array $options, $filter ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			array( 'otherthing' => 42 ),
			$options
		);
		$page = $pager->getPage( $filter );
		$this->assertInstanceOf( 'Flow\Data\Pager\PagerPage', $page, $message );
		$this->assertEquals( $expect, $page->getPagingLinksOptions(), $message );
	}

	public static function optionsPassedToObjectManagerFindProvider() {
		return array(
			array(
				'Requests one more object than pagination is for',
				// expect
				array( 'limit' => 3 ),
				// pager options
				array(
					'pager-limit' => 2,
				)
			),

			array(
				'Pager limit cannot be negative',
				// expect
				array( 'limit' => 2 ),
				// pager options
				array( 'pager-limit' => -99 ),
			),

			array(
				'Pager limit cannot exceed 500',
				// expect
				array( 'limit' => 2 ),
				// pager options
				array( 'pager-limit' => 501 ),
			),

			array(
				'Offset dir defaults to fwd',
				// expect
				array( 'offset-dir' => 'fwd' ),
				// pager options
				array(),
			),

			array(
				'Offset dir can be reversed',
				// expect
				array( 'offset-dir' => 'rev' ),
				// pager options
				array( 'pager-dir' => 'rev' ),
			),

			array(
				'Gracefully handles unknown offset dir',
				// expect
				array( 'offset-dir' => 'fwd' ),
				// pager options
				array( 'pager-dir' => 'yabba dabba do' ),
			),

			array(
				'offset-id defaults to null',
				// expect
				array( 'offset-id' => null ),
				// pager options
				array()
			),

			array(
				'initial offset-id is set by providing pager-offset',
				// expect
				array( 'offset-id' => 'echo and flow' ),
				// pager options
				array( 'pager-offset' => 'echo and flow' ),
			),
		);
	}

	/**
	 * @dataProvider optionsPassedToObjectManagerFindProvider
	 */
	public function testOptionsPassedToObjectManagerFind( $message, $expect, $options ) {
		$om = $this->mockObjectManager();
		$om->expects( $this->any() )
			->method( 'find' )
			->with( $this->anything(), $this->callback( function ( $opts ) use ( &$options ) {
				$options = $opts;
				return true;
			} ) );

		$pager = new Pager(
			$om,
			array( 'otherthing' => 42 ),
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

	public function includeOffsetProvider() {
		return array(
			array(
				'',
				// expected returned series of 'bar' values
				array( 5, 4, 3, 2, 1 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => true,
				),
			),
			array(
				'',
				// expected returned series of 'bar' values
				array( 4, 3, 2, 1 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => false,
				),
			),
			array(
				'',
				// expected returned series of 'bar' values
				array( 9, 8, 7, 6, 5 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => true,
					'offset-dir' => 'rev',
					'offset-elastic' => false,
				),
			),
			array(
				'',
				// expected returned series of 'bar' values
				array( 9, 8, 7, 6 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => false,
					'offset-dir' => 'rev',
					'offset-elastic' => false,
				),
			),
			array(
				'',
				// expected returned series of 'bar' values
				array( 9, 8, 7, 6, 5, 4, 3, 2, 1 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => true,
					'offset-dir' => 'rev',
					'offset-elastic' => true,
				),
			),
			array(
				'',
				// expected returned series of 'bar' values
				array( 9, 8, 7, 6, 5, 4, 3, 2, 1 ),
				// query options
				array(
					'offset-id' => 5,
					'include-offset' => false,
					'offset-dir' => 'rev',
					'offset-elastic' => true,
				),
			),

		);
	}

	/**
	 * @dataProvider includeOffsetProvider
	 */
	public function testIncludeOffset( $message, $expect, $queryOptions ) {
		global $wgFlowCacheVersion;

		$bag = new \HashBagOStuff();
		$innerCache = new LocalBufferedBagOStuff( $bag );
		$cache = new BufferedCache( $innerCache );

		// preload our answer
		$bag->set( wfWikiId() . ":prefix:1:$wgFlowCacheVersion", array(
			array( 'foo' => 1, 'bar' => 9 ),
			array( 'foo' => 1, 'bar' => 8 ),
			array( 'foo' => 1, 'bar' => 7 ),
			array( 'foo' => 1, 'bar' => 6 ),
			array( 'foo' => 1, 'bar' => 5 ),
			array( 'foo' => 1, 'bar' => 4 ),
			array( 'foo' => 1, 'bar' => 3 ),
			array( 'foo' => 1, 'bar' => 2 ),
			array( 'foo' => 1, 'bar' => 1 ),
		) );

		$storage = $this->getMock( 'Flow\Data\ObjectStorage' );

		$index = new TopKIndex(
			$cache,
			$storage,
			'prefix',
			array( 'foo' ),
			array(
				'sort' => 'bar',
			)
		);

		$result = $index->find( array( 'foo' => '1' ), $queryOptions );
		foreach ( $result as $row ) {
			$found[] = $row['bar'];
		}

		$this->assertEquals(
			$expect,
			$found
		);
	}

	protected function mockObjectManager( array $found = array() ) {
		$index = $this->getMock( 'Flow\Data\Index' );
		$index->expects( $this->any() )
			->method( 'getSort' )
			->will( $this->returnValue( array( 'something' ) ) );
		$om = $this->getMockBuilder( 'Flow\Data\ObjectManager' )
			->disableOriginalConstructor()
			->getMock();
		$om->expects( $this->any() )
			->method( 'getIndexFor' )
			->will( $this->returnValue( $index ) );
		$om->expects( $this->any() )
			->method( 'serializeOffset' )
			->will( $this->returnCallback( function( $obj, $sort ) {
				return 'serialized-' . $obj->foo;
			} ) );

		if ( $found ) {
			$om->expects( $this->any() )
				->method( 'find' )
				->will( call_user_func_array(
					array( $this, 'onConsecutiveCalls' ),
					array_map( array( $this, 'returnValue' ), $found )
				) );
		}

		return $om;
	}
}
