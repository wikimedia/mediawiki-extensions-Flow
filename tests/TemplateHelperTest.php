<?php

namespace Flow;

/**
 * @group Flow
 */
class TemplateHelperTest extends \MediaWikiTestCase {

	public function provideTraversalAttackFilenames() {
		return array_map( function( $x ) { return array( $x ); }, array(
			'.',
			'..',
			'./foo',
			'../foo',
			'foo/./bar',
			'foo/../bar',
			'foo/bar/.',
			'foo/bar/..',
		) );
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
		$code = TemplateHelper::compile( '{{#ifCond foo "or" bar}}Works{{/ifCond}}', '' );
		$renderer = \Lightncandy::prepare( $code );
		$this->assertEquals( $renderer( array( 'foo' => true, 'bar' => false ) ), 'Works' );
		$this->assertEquals( $renderer( array( 'foo' => false, 'bar' => false ) ), '' );
	}
}
