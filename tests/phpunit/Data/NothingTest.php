<?php

namespace Flow\Tests\Data;

use Flow\Data\Utils\SortArrayByKeys;
use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class FlowNothingTest extends FlowTestCase {

	public function sortArrayByKeysProvider() {
		return array(

			array(
				'Basic one key sort',
				// keys to sort by
				array( 'id' ),
				// array to sort
				array(
					array( 'id' => 5 ),
					array( 'id' => 7 ),
					array( 'id' => 6 ),
				),
				// expected result
				array(
					array( 'id' => 5 ),
					array( 'id' => 6 ),
					array( 'id' => 7 ),
				),
			),

			array(
				'Multi-key sort',
				// keys to sort by
				array( 'id', 'qq' ),
				// array to sort
				array(
					array( 'id' => 5, 'qq' => 4 ),
					array( 'id' => 5, 'qq' => 2 ),
					array( 'id' => 7, 'qq' => 1 ),
					array( 'id' => 6, 'qq' => 3 ),
					array( 'qq' => 9, 'id' => 4 ),
				),
				// expected result
				array(
					array( 'qq' => 9, 'id' => 4 ),
					array( 'id' => 5, 'qq' => 2 ),
					array( 'id' => 5, 'qq' => 4 ),
					array( 'id' => 6, 'qq' => 3 ),
					array( 'id' => 7, 'qq' => 1 ),
				),
			),

		);
	}

	/**
	 * @dataProvider sortArrayByKeysProvider
	 */
	public function testSortArrayByKeys( $message, array $keys, array $array, array $sorted, $strict = true ) {
		usort( $array, new SortArrayByKeys( $keys, $strict ) );
		$this->assertEquals( $sorted, $array );
	}
}
