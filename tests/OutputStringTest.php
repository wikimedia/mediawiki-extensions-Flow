<?php

namespace Flow\Tests;

use Flow\Template\OutputString;
use Flow\Template\TextString;
use Flow\Template\HtmlString;

class OutputStringTest extends \MediaWikiTestCase {

	public function testTextStringWithPlainValue() {
		$string = new TextString( 'zomg' );
		$this->assertEquals( 'zomg', $string->text() );
		$this->assertEquals( 'zomg', $string->escaped() );
		$this->assertEquals( 'zomg', $string->parse() );
	}

	public function testTextStringWithValueNeedingEscape() {
		$val = '<script>alert( \'evil\' );</script>';
		$escaped = htmlspecialchars( $val );
		$string = new TextString( $val );
		$this->assertEquals( $val, $string->text() );
		$this->assertEquals( $escaped, $string->escaped() );
		$this->assertEquals( $escaped, $string->parse() );
	}

	public function testHtmlString() {
		$string = new HtmlString( 'zomg' );
		$this->assertEquals( 'zomg', $string->text() );
		$this->assertEquals( 'zomg', $string->escaped() );
		$this->assertEquals( 'zomg', $string->parse() );
	}

	public function testHtmlStringPassesThrough() {
		$val = '<script>alert( \'gumballs\' )</script>';
		$string = new HtmlString( $val );
		$this->assertEquals( $val, $string->escaped() );
		$this->assertEquals( $val, $string->parse() );
	}
}
