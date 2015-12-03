<?php

namespace Flow\Tests\Data\Pager;

use Flow\Data\Pager\Pager;
use Flow\Model\UUID;

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
				'Reverse pagination when offset is present in options',
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
				'offset-value defaults to null',
				// expect
				array( 'offset-value' => null ),
				// pager options
				array()
			),

			array(
				'initial offset-value is set by providing pager-offset',
				// expect
				array( 'offset-value' => 'echo and flow' ),
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

	public function provideDataMakePagingLink() {
		return array (
			array(
				$this->mockStorage(
					array(
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry()
					),
					UUID::create(),
					array( 'topic_id' )
				),
				array( 'topic_list_id' => '123456' ),
				array( 'pager-limit' => 2, 'order' => 'desc', 'sort' => 'topic_id' ),
				'offset-id'
			),
			array(
				$this->mockStorage(
					array(
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry()
					),
					UUID::create(),
					array( 'workflow_last_update_timestamp' )
				),
				array( 'topic_list_id' => '123456' ),
				array( 'pager-limit' => 1, 'order' => 'desc', 'sort' => 'workflow_last_update_timestamp', 'sortby' => 'updated' ),
				'offset'
			)
		);
	}

	/**
	 * @dataProvider provideDataMakePagingLink
	 */
	public function testMakePagingLink( $storage, $query, $options, $offsetKey ) {
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
	 * Mock the storage
	 */
	protected function mockStorage( $return, $offset, $sort ) {
		$storage = $this->getMockBuilder( 'Flow\Data\ObjectManager' )
			->disableOriginalConstructor()
			->getMock();
		$storage->expects( $this->any() )
			->method( 'find' )
			->will( $this->returnValue( $return ) );
		$storage->expects( $this->any() )
			->method( 'serializeOffset' )
			->will( $this->returnValue( $offset ) );
		$storage->expects( $this->any() )
			->method( 'getIndexFor' )
			->will( $this->returnValue( $this->mockIndex( $sort ) ) );
		return $storage;
	}

	/**
	 * Mock TopicListEntry
	 */
	protected function mockTopicListEntry() {
		$entry = $this->getMockBuilder( 'Flow\Model\TopicListEntry' )
			->disableOriginalConstructor()
			->getMock();
		return $entry;
	}

	/**
	 * Mock TopKIndex
	 */
	protected function mockIndex( $sort ) {
		$index = $this->getMockBuilder( 'Flow\Data\Index\TopKIndex' )
			->disableOriginalConstructor()
			->getMock();
		$index->expects( $this->any() )
			->method( 'getSort' )
			->will( $this->returnValue( $sort ) );
		return $index;
	}
}
