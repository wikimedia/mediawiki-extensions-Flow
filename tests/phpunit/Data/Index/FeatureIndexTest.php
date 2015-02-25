<?php

namespace Flow\Tests\Data\Index;

use Flow\Data\Index\FeatureIndex;

/**
 * @group Flow
 */
class FeatureIndexTest extends \MediaWikiTestCase {

	public function testOffsetIdReturnsCorrectPortionOfIndexedValues() {
		global $wgFlowCacheVersion;
		$cache = $this->getMockBuilder( 'Flow\Data\BufferedCache' )
			->disableOriginalConstructor()
			->getMock();
		$storage = $this->getMockBuilder( 'Flow\Data\ObjectStorage' )
			->disableOriginalConstructor()
			->getMock();

		$dbId = FeatureIndex::cachedDbId();
		$cache->expects( $this->any() )
			->method( 'getMulti' )
			->will( $this->returnValue( array(
				"$dbId:foo:5:$wgFlowCacheVersion" => array(
					array( 'some_row' => 40 ),
					array( 'some_row' => 41 ),
					array( 'some_row' => 42 ),
					array( 'some_row' => 43 ),
					array( 'some_row' => 44 ),
				),
			) ) );
		$storage->expects( $this->never() )
			->method( 'findMulti' );

		$index = new MockFeatureIndex( $cache, $storage, 'foo', array( 'bar' ) );

		$res = $index->find(
			array( 'bar' => 5 ),
			array( 'offset-id' => 42 )
		);

		$this->assertEquals(
			array(
				array( 'some_row' => 43, 'bar' => 5 ),
				array( 'some_row' => 44, 'bar' => 5 ),
			),
			array_values( $res ),
			'Returns items with some_row > provided offset-id of 42'
		);
	}

	public function testReversePagination() {
		global $wgFlowCacheVersion;
		$cache = $this->getMockBuilder( 'Flow\Data\BufferedCache' )
			->disableOriginalConstructor()
			->getMock();
		$storage = $this->getMockBuilder( 'Flow\Data\ObjectStorage' )
			->disableOriginalConstructor()
			->getMock();

		$dbId = FeatureIndex::cachedDbId();
		$cache->expects( $this->any() )
			->method( 'getMulti' )
			->will( $this->returnValue( array(
				"$dbId:foo:5:$wgFlowCacheVersion" => array(
					array( 'some_row' => 40 ),
					array( 'some_row' => 41 ),
					array( 'some_row' => 42 ),
					array( 'some_row' => 43 ),
					array( 'some_row' => 44 ),
				),
			) ) );
		$storage->expects( $this->never() )
			->method( 'findMulti' );

		$index = new MockFeatureIndex( $cache, $storage, 'foo', array( 'bar' ) );

		$res = $index->find(
			array( 'bar' => 5 ),
			array( 'offset-id' => 43, 'offset-dir' => 'rev', 'limit' => 2 )
		);
		$this->assertEquals(
			array(
				array( 'some_row' => 41, 'bar' => 5 ),
				array( 'some_row' => 42, 'bar' => 5 ),
			),
			array_values( $res ),
			'Data should retain original sort, taking selected items from before the offset'
		);
	}
}
