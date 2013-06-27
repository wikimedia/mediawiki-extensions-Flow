<?php

namespace Flow;

class IndexTest extends \MediaWikiTestCase {

	public function testShallow() {
		$bag = new \HashBagOStuff;
		$cache = new \Flow\Data\BufferedCache( $bag );
		$storage = $this->getMock( 'Flow\\Data\\ObjectStorage' );

		$unique = new \Flow\Data\UniqueIndex(
			$cache, $storage, 'unique',
			array( 'id' )
		);

		$secondary = new \Flow\Data\SecondaryIndex(
			$cache, $storage, 'secondary',
			array( 'name' ), // keys indexed in this array
			array(
				'shallow' => $unique,
				'sort' => 'id',
			)
		);

		$bag->set( 'flow:unique:1', array( array( 'id' => 1, 'name' => 'foo', 'other' => 'ppp' ) ) );
		$bag->set( 'flow:unique:2', array( array( 'id' => 2, 'name' => 'foo', 'other' => 'qqq' ) ) );
		$bag->set( 'flow:unique:3', array( array( 'id' => 3, 'name' => 'baz', 'other' => 'lll' ) ) );

		$bag->set( 'flow:secondary:foo', array( array( 'id' => 1 ), array( 'id' => 2 ) ) );
		$bag->set( 'flow:secondary:baz', array( array( 'id' => 3 ) ) );

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
		$bag = new \HashBagOStuff;
		$cache = new \Flow\Data\BufferedCache( $bag );
		$storage = $this->getMock( 'Flow\\Data\\ObjectStorage' );

		$unique = new \Flow\Data\UniqueIndex(
			$cache, $storage, 'unique',
			array( 'id', 'ot' )
		);

		$secondary = new \Flow\Data\SecondaryIndex(
			$cache, $storage, 'secondary',
			array( 'name' ), // keys indexed in this array
			array(
				'shallow' => $unique,
				'sort' => 'id',
			)
		);

		// remember: unique index still stores an array of results to be consistent with other indexes
		// even though, due to uniqueness, there is only one value per set of keys
		$bag->set( 'flow:unique:1:9', array( array( 'id' => 1, 'ot' => 9, 'name' => 'foo' ) ) );
		$bag->set( 'flow:unique:1:8', array( array( 'id' => 1, 'ot' => 8, 'name' => 'foo' ) ) );
		$bag->set( 'flow:unique:3:7', array( array( 'id' => 3, 'ot' => 7, 'name' => 'baz' ) ) );

		$bag->set( 'flow:secondary:foo', array(
			array( 'id' => 1, 'ot' => 9 ),
			array( 'id' => 1, 'ot' => 8 ),
		) );
		$bag->set( 'flow:secondary:baz', array(
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


