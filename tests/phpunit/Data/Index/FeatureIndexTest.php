<?php

namespace Flow\Tests\Data\Index;

use Flow\Data\Index\FeatureIndex;

/**
 * @group Flow
 */
class UniqueFeatureIndexTests extends \MediaWikiTestCase {

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
