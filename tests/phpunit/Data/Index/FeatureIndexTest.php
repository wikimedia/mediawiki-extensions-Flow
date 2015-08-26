<?php

namespace Flow\Tests\Data\Index;

use Flow\Data\Index\FeatureIndex;
use Flow\Data\ObjectMapper;

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
		// fake ObjectMapper that doesn't roundtrip to- & fromStorageRow
		$mapper = $this->getMockBuilder( 'Flow\Data\Mapper\BasicObjectMapper' )
			->disableOriginalConstructor()
			->getMock();
		$mapper->expects( $this->any() )
			->method( 'normalizeRow' )
			->will( $this->returnArgument( 0 ) );

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

		$index = new MockFeatureIndex( $cache, $storage, $mapper, 'foo', array( 'bar' ) );

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
		// fake ObjectMapper that doesn't roundtrip to- & fromStorageRow
		$mapper = $this->getMockBuilder( 'Flow\Data\Mapper\BasicObjectMapper' )
			->disableOriginalConstructor()
			->getMock();
		$mapper->expects( $this->any() )
			->method( 'normalizeRow' )
			->will( $this->returnArgument( 0 ) );

		$index = new MockFeatureIndex( $cache, $storage, $mapper, 'foo', array( 'bar' ) );

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

class MockFeatureIndex extends FeatureIndex {
	public function getLimit() { return 42; }
	public function queryOptions() { return array(); }
	public function limitIndexSize( array $values ) { return $values; }
	public function addToIndex( array $indexed, array $row ) {}
	public function removeFromIndex( array $indexed, array $row ) {}

	// not abstract, but override for convenience
	public function getSort() { return array( 'some_row' ); }
	public function getOrder() { return 'ASC'; }
}
