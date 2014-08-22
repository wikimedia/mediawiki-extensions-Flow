<?php

namespace Flow;

use MediaWikiTestCase;
use Flow\Exception\WikitextException;
use Flow\Parsoid\Utils;
use ReflectionMethod;
use Title;

/**
 * @group Database
 * @group Flow
 */
class ReferenceExtractorTestCase extends MediaWikiTestCase {
	public function setUp() {
		parent::setUp();

		// Check for Parsoid
		try {
			Utils::convert( 'html', 'wikitext', 'Foo', Title::newFromText( 'UTPage' ) );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
	}

	public static function referenceExtractorProvider() {
		return array(
			array(
				'Normal link',
				// source wiki text
				'[[My page]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'link', 'My page' ),
			),
			array(
				'Link with URL encoding issues',
				// source wiki text
				'[[User talk:Werdna?]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'link', 'User talk:Werdna?' ),
			),
			array(
				'Subpage link',
				// source wiki text
				'[[/Subpage]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'link', '/Subpage' ),
				// ???
				'Talk:UTPage',
			),
			array(
				'External link',
				// source wiki text
				'[http://www.google.com Google]',
				// expected factory method
				'createUrlReference',
				// exepcted factory arguments
				array( 'link', 'http://www.google.com' ),
			),
			array(
				'File',
				// source wiki text
				'[[File:Image.png]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'file', 'File:Image.png' ),
			),
			array(
				'File with parameters',
				// source wiki text
				'[[File:Image.png|25px]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'file', 'File:Image.png' ),
			),
			array(
				'Template',
				// source wiki text
				'{{Foo}}',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'template', 'Template:Foo' ),
			),

			array(
				'Non-existant File',
				// source wiki text
				'[[File:Some/Files/Really/Should_Not_Ex/ist.png]]',
				// expected factory method
				'createWikiReference',
				// expected factory arguments
				array( 'file', 'File:Some/Files/Really/Should_Not_Ex/ist.png' ),
			)
		);
	}

	/**
	 * @dataProvider referenceExtractorProvider
	 */
	public function testReferenceExtractor( $description, $wikitext, $expectedMethod, $expectedArguments, $page = 'UTPage' ) {
		$referenceExtractor = Container::get( 'reference.extractor' );

		$html = Utils::convert( 'wt', 'html', $wikitext, Title::newFromText( $page ) );
		$factory = $this->getMockBuilder( 'Flow\Parsoid\ReferenceFactory' )
			->disableOriginalConstructor()
			->getMock();

		$factory->expects( $this->once() )
			->method( $expectedMethod )
			->getMatcher()
			->parametersMatcher = new \PHPUnit_Framework_MockObject_Matcher_Parameters( $expectedArguments );

		$reflMethod = new ReflectionMethod( $referenceExtractor, 'extractReferences' );
		$reflMethod->setAccessible( true );
		$reflMethod->invoke( $referenceExtractor, $factory, $html );
	}
}
