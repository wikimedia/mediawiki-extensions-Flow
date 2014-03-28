<?php

namespace Flow\Tests;

use Flow\Data\MultiDimArray;

class MultiDimArrayTest extends \MediaWikiTestCase {

	public function testIteration() {
		$multi = new MultiDimArray;
		$multi[] = array(
			'foo' => array(
				'bar ' => array( 1,2,3 ),
				'baz' => array( 4,5,6 ),
			),
			'batman' => array(
				'robin' => array( 7,8,9 ),
			),
		);
		foreach ( $multi as $key => $value ) {
			var_dump( $key );
			var_dump( $value );
			echo "\n\n";
		}
		die( 'xxx' );
	}
}
