<?php

namespace Flow;

use MediaWikiTestCase;
use Flow\Container;
use Title;

class ReferenceExtractorTestCase extends MediaWikiTestCase {
	public static function referenceExtractorProvider() {
		return array(
			array(
				'[[My page]]',
				array(
					array(
						'refType' => 'link',
						'targetType' => 'wiki',
						'target' => 'My_page',
					),
				)
			),
			array(
				'[http://www.google.com Google]',
				array(
					array(
						'refType' => 'link',
						'targetType' => 'url',
						'target' => 'http://www.google.com',
					),
				)
			),
			array(
				'[[File:Image.png]]',
				array(
					array(
						'refType' => 'file',
						'targetType' => 'wiki',
						'target' => 'File:Image.png',
					),
				)
			),
			array(
				'[[File:Image.png|25px]]',
				array(
					array(
						'refType' => 'file',
						'targetType' => 'wiki',
						'target' => 'File:Image.png',
					),
				)
			),
			array(
				'{{Foo}}',
				array(
					array(
						'refType' => 'template',
						'targetType' => 'wiki',
						'target' => 'Template:Foo',
					),
				)
			),
		);
	}

	/**
	 * @dataProvider referenceExtractorProvider
	 */
	public function testReferenceExtractor( $wikitext, $expectedOutput ) {
		$referenceExtractor = Container::get( 'reference.extractor' );

		$html = ParsoidUtils::convert( 'wt', 'html', $wikitext, Title::newFromText( 'Test page' ) );

		$references = $referenceExtractor->extractReferences( $html );

		$this->assertEquals( $references, $expectedOutput );
	}
}