<?php

namespace Flow\Tests\Data\Pager;

use Flow\Data\Pager\Pager;
use stdClass;

class PagerTest extends \MediaWikiTestCase {

	public static function getPageResultsProvider() {
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
				// query options,
				array(),
			),

			array(
				'Returns found objects',
				// expect
				array( $objs['A'], $objs['B'] ),
				// find results
				array( $objs['A'], $objs['B'] ),
				// query options
				array( 'pager-limit' => 10 ),
			),
		);
	}

	/**
	 * @dataProvider getPageResultsProvider
	 */
	public function testGetPageResults( $message, $expect, $found, array $options ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			array( 'otherthing' => 42 ),
			$options
		);
		$page = $pager->getPage();
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
			),

			array(
				'No next page with exact number of results',
				// expect
				array(),
				// find results
				array( $objs['A'], $objs['B'] ),
				// pager options
				array( 'pager-limit' => 2 ),
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
				array( $objs['A'], $objs['B'], $objs['C'] ),
				// pager options
				array( 'pager-limit' => 2 ),
			),

			array(
				'Reverse pagination when pager-offset is present in options',
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
				array( $objs['B'], $objs['C'], $objs['D'] ),
				// pager options
				array(
					'pager-limit' => 2,
					'pager-offset' => 'serialized-A',
					'pager-dir' => 'fwd',
				),
			),

		);
	}

	/**
	 * @dataProvider getPagingLinkOptionsProvider
	 */
	public function testGetPagingLinkOptions( $message, $expect, $found, array $options ) {
		$pager = new Pager(
			$this->mockObjectManager( $found ),
			array( 'otherthing' => 42 ),
			$options
		);
		$page = $pager->getPage();
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
				array( 'offset-key' => null ),
				// pager options
				array()
			),

			array(
				'initial offset-id is set by providing pager-offset',
				// expect
				array( 'offset-key' => 'echo and flow' ),
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
				->will( $this->returnValue( $found ) );
		}

		return $om;
	}
}
