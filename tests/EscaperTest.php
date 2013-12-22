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
	}

	public function testValueAddedToArrayIsEscaped() {
		$escaped = Escaper::__escape( array() );
		$escaped['winner'] = 'chicken dinner';
		$this->assertTrue( isset( $escaped['winner'] ) );
		$this->assertEquals( new TextString( 'chicken dinner' ), $escaped['winner'] );
	}

	public function testArraysAreForeachable() {
		$escaped = Escaper::__escape( array( 'bing' => 'bang' ) );
		$count = 0;
		foreach ( $escaped as $key => $value ) {
			$this->assertEquals( 'bing', $key );
			$this->assertEquals( new TextString( 'bang' ), $value );
			++$count;
		}
		$this->assertEquals( 1, $count );
	}

	static public function escapedValueProvider() {
		return array(
			array( 'booleans true', true, '1' ),
			array( 'booleans false', false, ''),
			array( 'single quote', "'", "'" ),
			array( 'double quote', '"', '&quot;' ),
			array( 'html tags', '<a>', '&lt;a&gt;' ),
			array( 'null value', null, '' ),
		);
	}

	/**
	 * @dataProvider escapedValueProvider
	 */
	public function testEscapedValue( $msg, $input, $output ) {
		$this->assertEquals( $output, Escaper::__escape( $input )->sanitized(), $msg );
	}

	static public function rawValueProvider() {
		$obj = new \stdClass;
		$obj->foo = '42';

		return array(
			array( 'boolean true cast to string', true, '1' ),
			array( 'boolean false cast to string', false, '' ),
			array( 'null value passes through', null, null ),
			array( 'object value passes through', $obj, $obj ),
		);
	}

	/**
	 * @dataProvider rawValueProvider
	 */
	public function testRawValue( $msg, $input, $output ) {
		$this->assertEquals( $output, Escaper::__escape( $input )->__raw(), $msg );
	}

}
