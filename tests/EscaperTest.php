<?php

namespace Flow\Tests;

use Flow\Template\Escaper;
use Flow\Template\TextString;

class EscaperTest extends \MediaWikiTestCase {

	public function testGetSetIssetUnset() {
		$escaped = Escaper::__escape( (object)array() );
		$this->assertFalse( isset( $escaped->someproperty ) );
		$escaped->someproperty = 'naismith';
		$this->assertTrue( isset( $escaped->someproperty ) );
		$this->assertEquals( new TextString( 'naismith' ), $escaped->someproperty );
		unset( $escaped->someproperty );
		$this->assertFalse( isset( $escaped->someproperty ) );

		try {
			$escaped->someproperty;
			$this->fail( 'someproperty must not exist' );
		} catch ( \PHPUnit_Framework_Error_Notice $e ) {
			$this->assertEquals( 'Undefined property: stdClass::$someproperty', $e->getMessage() );
		}
	}

	public function testArrayAccess() {
		$escaped = Escaper::__escape( array(
			'foo' => 'bar',
			'baz' => 'blimp',
		) );

		$this->assertTrue( isset( $escaped['foo'] ) );
		$this->assertEquals( new TextString( 'bar' ), $escaped['foo'] );
		$this->assertEquals( new TextString( 'blimp'), $escaped['baz'] );
		unset( $escaped['foo'] );
		$this->assertFalse( isset( $escaped['foo'] ) );
		$escaped['winner'] = 'chicken dinner';
		$this->assertTrue( isset( $escaped['winner'] ) );
		$this->assertEquals( new TextString( 'chicken dinner' ), $escaped['winner'] );
	}
}
