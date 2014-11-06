<?php

namespace Flow\Tests\Model;

use Flow\Model\UserTuple;

/**
 * @group Flow
 */
class UserTupleTest extends \MediaWikiTestCase {

	public function invalidInputProvider() {
		return array(
			array( 'foo', 0, ''),
			array( 'foo', 1234, '127.0.0.1' ),
			array( '', 0, '127.0.0.1' ),
			array( 'foo', -25, '' ),
			array( 'foo', null, '127.0.0.1' ),
			array( null, 55, '' ),
			array( 'foo', 0, null ),
		);
	}

	/**
	 * @dataProvider invalidInputProvider
	 * @expectedException Flow\Exception\InvalidDataException
	 */
	public function testInvalidInput( $wiki, $id, $ip ) {
		new UserTuple( $wiki, $id, $ip );
	}

	public function validInputProvider() {
		return array(
			array( 'foo', 42, null ),
			array( 'foo', 42, '' ),
			array( 'foo', 0, '127.0.0.1' ),
			array( 'foo', '0', '10.1.2.3' ),
		);
	}

	/**
	 * @dataProvider validInputProvider
	 */
	public function testValidInput( $wiki, $id, $ip ) {
		new UserTuple( $wiki, $id, $ip );
		// no error thrown from constructor
		$this->assertTrue( true );
	}
}
