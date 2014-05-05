<?php

namespace Flow;

use MediaWikiTestCase;
use Flow\Container;
use Flow\Exception\WikitextException;
use Title;
use User;
use WikiPage;
use WikitextContent;

/**
 * @group Database
 */
class ReferenceExtractorTestCase extends MediaWikiTestCase {
	public function setUp() {
		parent::setUp();

		// Check for Parsoid
		try {
			\Flow\Parsoid\Utils::convert( 'html', 'wikitext', 'Foo', Title::newFromText( 'UTPage' ) );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
	}

	public static function referenceExtractorProvider() {
		return array(
			array(
				'Normal link',
				'[[My page]]',
				array(
					array(
						'refType' => 'link',
						'targetType' => 'wiki',
						'target' => 'My page',
					),
				)
			),
			array(
				'Link with URL encoding issues',
				'[[User talk:Werdna?]]',
				array(
					array(
						'refType' => 'link',
						'targetType' => 'wiki',
						'target' => 'User talk:Werdna?',
					),
				),
			),
			array(
				'Subpage link',
				'[[/Subpage]]',
				array(
					array(
						'refType' => 'link',
						'targetType' => 'wiki',
						'target' => '/Subpage'
					),
				),
				'Talk:UTPage',
			),
			array(
				'External link',
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
				'File',
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
				'File with parameters',
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
				'Template',
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
	public function testReferenceExtractor( $description, $wikitext, $expectedOutput, $page = 'UTPage' ) {
		$referenceExtractor = Container::get( 'reference.extractor' );

		$html = \Flow\Parsoid\Utils::convert( 'wt', 'html', $wikitext, Title::newFromText( $page ) );

		$references = $referenceExtractor->extractReferences( $html );

		$this->assertEquals( $references, $expectedOutput, $html );
	}
}
