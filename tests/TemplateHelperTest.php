<?php

namespace Flow;

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
}
