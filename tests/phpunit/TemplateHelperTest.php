<?php

namespace Flow\Tests;

use Lightncandy;
use Flow\TemplateHelper;

/**
 * @group Flow
 */
class TemplateHelperTest extends \MediaWikiTestCase {

	public function provideTraversalAttackFilenames() {
		return array_map(
			function ( $x ) {
				return [ $x ];
			},
			[
				'.',
				'..',
				'./foo',
				'../foo',
				'foo/./bar',
				'foo/../bar',
				'foo/bar/.',
				'foo/bar/..',
			]
		);
	}

	/**
	 * @dataProvider provideTraversalAttackFilenames
	 * @expectedException \Flow\Exception\FlowException
	 */
	public function testGetTemplateFilenamesTraversalAttack( $templateName ) {
		$helper = new TemplateHelper( '/does/not/exist' );
		$helper->getTemplateFilenames( $templateName );
	}

	public function testIfCond() {
		$code = TemplateHelper::compile( "{{#ifCond foo \"or\" bar}}Works{{/ifCond}}", '' );
		$renderer = Lightncandy::prepare( $code );

		$this->assertEquals( 'Works', $renderer( [ 'foo' => true, 'bar' => false ] ) );
		$this->assertEquals( '', $renderer( [ 'foo' => false, 'bar' => false ] ) );
		/*
		FIXME: Why won't this work!?
		$code2 = TemplateHelper::compile( "{{#ifCond foo \"===\" bar}}Works{{/ifCond}}", '' );
		$renderer2 = Lightncandy::prepare( $code2 );
		$this->assertEquals( 'Works', $renderer2( array( 'foo' => 1, 'bar' => 1 ) ) );
		$this->assertEquals( '', $renderer2( array( 'foo' => 2, 'bar' => 3 ) ) );*/
	}
}
