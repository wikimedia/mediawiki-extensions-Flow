<?php

namespace Flow\Tests\Data;

use Flow\Container;
use Flow\Data\BagOStuff\BufferedBagOStuff;
use Flow\Data\BufferedCache;
use Flow\Data\Index\FeatureIndex;
use Flow\Data\Index\TopKIndex;
use Flow\Data\Index\UniqueFeatureIndex;
use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class IndexTest extends FlowTestCase {

	public function testShallow() {
		global $wgFlowCacheTime;

		$bag = new BufferedBagOStuff( new \HashBagOStuff );
		$cache = new BufferedCache( $bag, $wgFlowCacheTime );

		// fake ObjectMapper that doesn't roundtrip to- & fromStorageRow
		$mapper = $this->getMockBuilder( 'Flow\Data\Mapper\BasicObjectMapper' )
			->disableOriginalConstructor()
			->getMock();
		$mapper->expects( $this->any() )
			->method( 'normalizeRow' )
			->will( $this->returnArgument( 0 ) );

		// As we are only testing the cached result, storage should never be called
		// not sure how to test that
		$storage = $this->getMock( 'Flow\\Data\\ObjectStorage' );

		$unique = new UniqueFeatureIndex(
			$cache, $storage, $mapper, 'unique',
			array( 'id' )
		);

		$secondary = new TopKIndex(
			$cache, $storage, $mapper, 'secondary',
			array( 'name' ), // keys indexed in this array
			array(
				'shallow' => $unique,
				'sort' => 'id',
			)
		);

		$db = FeatureIndex::cachedDbId();
		$v = Container::get( 'cache.version' );
		$bag->set( "$db:unique:" . md5( '1' ) . ":$v", array( array( 'id' => 1, 'name' => 'foo', 'other' => 'ppp' ) ) );
		$bag->set( "$db:unique:" . md5( '2' ) . ":$v", array( array( 'id' => 2, 'name' => 'foo', 'other' => 'qqq' ) ) );
		$bag->set( "$db:unique:" . md5( '3' ) . ":$v", array( array( 'id' => 3, 'name' => 'baz', 'other' => 'lll' ) ) );

		$bag->set( "$db:secondary:" . md5( 'foo' ) . ":$v", array( array( 'id' => 1 ), array( 'id' => 2 ) ) );
		$bag->set( "$db:secondary:" . md5( 'baz' ) . ":$v", array( array( 'id' => 3 ) ) );

		$expect = array(
			array( 'id' => 1, 'name' => 'foo', 'other' => 'ppp', ),
			array( 'id' => 2, 'name' => 'foo', 'other' => 'qqq', ),
		);
		$this->assertEquals( $expect, $secondary->find( array( 'name' => 'foo' ) ) );

		$expect = array(
			array( 'id' => 3, 'name' => 'baz', 'other' => 'lll' ),
		);
		$this->assertEquals( $expect, $secondary->find( array( 'name' => 'baz' ) ) );
	}

	public function testCompositeShallow() {
		global $wgFlowCacheTime;

		$bag = new BufferedBagOStuff( new \HashBagOStuff );
		$cache = new BufferedCache( $bag, $wgFlowCacheTime );
		$storage = $this->getMock( 'Flow\\Data\\ObjectStorage' );

		// fake ObjectMapper that doesn't roundtrip to- & fromStorageRow
		$mapper = $this->getMockBuilder( 'Flow\Data\Mapper\BasicObjectMapper' )
			->disableOriginalConstructor()
			->getMock();
		$mapper->expects( $this->any() )
			->method( 'normalizeRow' )
			->will( $this->returnArgument( 0 ) );

		$unique = new UniqueFeatureIndex(
			$cache, $storage, $mapper, 'unique',
			array( 'id', 'ot' )
		);

		$secondary = new TopKIndex(
			$cache, $storage, $mapper, 'secondary',
			array( 'name' ), // keys indexed in this array
			array(
				'shallow' => $unique,
				'sort' => 'id',
			)
		);

		// remember: unique index still stores an array of results to be consistent with other indexes
		// even though, due to uniqueness, there is only one value per set of keys
		$db = FeatureIndex::cachedDbId();
		$v = Container::get( 'cache.version' );
		$bag->set( "$db:unique:" . md5( '1:9' ) . ":$v", array( array( 'id' => 1, 'ot' => 9, 'name' => 'foo' ) ) );
		$bag->set( "$db:unique:" . md5( '1:8' ) . ":$v", array( array( 'id' => 1, 'ot' => 8, 'name' => 'foo' ) ) );
		$bag->set( "$db:unique:" . md5( '3:7' ) . ":$v", array( array( 'id' => 3, 'ot' => 7, 'name' => 'baz' ) ) );

		$bag->set( "$db:secondary:" . md5( 'foo' ) . ":$v", array(
			array( 'id' => 1, 'ot' => 9 ),
			array( 'id' => 1, 'ot' => 8 ),
		) );
		$bag->set( "$db:secondary:" . md5( 'baz' ). ":$v", array(
			array( 'id' => 3, 'ot' => 7 ),
		) );

		$expect = array(
			array( 'id' => 1, 'ot' => 9, 'name' => 'foo' ),
			array( 'id' => 1, 'ot' => 8, 'name' => 'foo' ),
		);
		$this->assertEquals( $expect, $secondary->find( array( 'name' => 'foo' ) ) );

		$expect = array(
			array( 'id' => 3, 'ot' => 7, 'name' => 'baz' ),
		);
		$this->assertEquals( $expect, $secondary->find( array( 'name' => 'baz' ) ) );
	}
}
