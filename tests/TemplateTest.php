<?php

namespace Flow\Tests;

use Flow\Template;
use Flow\Template\TextString;

class TemplateTest extends \MediaWikiTestCase {

	public function testGetSetIssetUnset() {
		$template = new Template();
		$this->assertFalse( isset( $template->someproperty ) );
		$template->someproperty = 'foo';
		$this->assertTrue( isset( $template->someproperty ) );
		$this->assertEquals( new TextString( 'foo' ), $template->someproperty );
		unset( $template->someproperty );
		$this->assertFalse( isset( $template->someproperty ) );

		try {
			$template->someproperty;
			$this->fail( 'someproperty must not exist' );
		} catch ( \PHPUnit_Framework_Error_Notice $e ) {
			$this->assertEquals( 'Undefined index: someproperty', $e->getMessage() );
		}
	}

	public function testGetHelper() {
		$helpers = array(
			'something' => function() {
				return 'helper';
			},
		);
		$template = new Template( array(), $helpers );
		$this->assertEquals( new TextString( 'helper' ), $template->something() );
	}

	public function testHelperWithArguments() {
		$helpers = array(
			'otherthing' => function( $arg ) {
				return $arg;
			},
		);
		$template = new Template( array(), $helpers );
		$this->assertEquals( new TextString( 'myhelper' ), $template->otherthing( 'myhelper' ) );
	}

	public function testSetAndAddData() {
		$template = new Template();
		$this->assertFalse( isset( $template->vor ) );
		$template->setData( array( 'vor' => 123, 'lord' => 456 ) );
		$this->assertTrue( isset( $template->vor ) );
		$this->assertEquals( new TextString( '123' ), $template->vor );
		$this->assertEquals( new TextString( '456' ), $template->lord );
		$template->addData( array( 'lord' => 789, 'miles' => 321 ) );
		$this->assertEquals( new TextString( '789' ), $template->lord );
		$this->assertEquals( new TextString( '321' ), $template->miles );
	}

	public function testUnconfiguredFindFile() {
		$template = new Template();
		$this->assertEquals(
			'foo.html.php',
			$template->findFile( 'foo.html.php' ),
			'With no namespace requested pass through the input string'
		);
	}

	/**
	 * @expectedException Flow\Exception\RuntimeException
	 */
	public function testUnconfiguredNamespacedFindFile() {
		$template = new Template();
		$template->findFile( 'foo:bar.html.php' );
	}

	public function testConfiguredNamespacedFindFile() {
		$template = new Template( array( 'foo' => '/tmp', 'bar' => '/media' ) );
		$this->assertEquals(
			'/tmp/bazinga.html.php',
			$template->findFile( 'foo:bazinga.html.php' )
		);
		$this->assertEquals(
			'/media/bazinga.html.php',
			$template->findFile( 'bar:bazinga.html.php' )
		);
	}

	public function testRawDataIsAvailable() {
		$data = array( 'orig' => 'data', 'is' => 'not <escaped>' );
		$template = new Template( array(), array(), $data );
		$this->assertEquals( $data, $template->__raw() );

		$data += array( 'hi' => 'there' );
		$template->setData( $data );
		$this->assertEquals( $data, $template->__raw() );
	}
}
