<?php

namespace Flow\Tests\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Exception\WikitextException;
use Flow\Import\Wikitext\ImportSource;
use Flow\Conversion\Utils;
use Parser;
use Title;
use WikiPage;
use WikitextContent;

/**
 * @group Flow
 * @group Database
 */
class ImportSourceTest extends \MediaWikiTestCase {

	protected $tablesUsed = array( 'page', 'revision' );

	protected function setUp() {
		parent::setUp();

		// Check for Parsoid
		try {
			Utils::convert( 'html', 'wikitext', 'Foo', Title::newFromText( 'UTPage' ) );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
	}

	/**
	 * @dataProvider getHeaderProvider
	 */
	public function testGetHeader( $content, $expect ) {
		// create a page with some content
		$status = WikiPage::factory( Title::newMainPage() )
			->doEditContent(
				new WikitextContent( $content ),
				"and an edit summary"
			);
		if ( !$status->isGood() ) {
			$this->fail( $status->getMessage()->plain() );
		}

		$source = new ImportSource( Title::newMainPage(), new Parser );
		$header = $source->getHeader();
		$this->assertNotNull( $header );
		$this->assertGreaterThan( 1, strlen( $header->getObjectKey() ) );

		$revisions = iterator_to_array( $header->getRevisions() );
		$this->assertCount( 1, $revisions );

		$revision = reset( $revisions );
		$this->assertInstanceOf( 'Flow\Import\IObjectRevision', $revision );
		$this->assertEquals( $expect, $revision->getText() );
	}

	public function getHeaderProvider() {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$date = $now->format( 'Y-m-d' );

		return array(
			array(
				// original page content
				"This is some content\n",
				// content to be stored to header
				"\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"{{tpl}}\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"{{tpl}}\nNon-template text\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"Non-template text\n{{tpl}}\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"Non-template text\n{{tpl}}\nNon-template text\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"{{tpl}}\nNon-template text\n{{tpl}}\nNon-template text\n{{tpl}}\n",
				"{{tpl}}\n{{tpl}}\n{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"{{tpl\n|key=value}}\n",
				"{{tpl\n|key=value}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			),
			array(
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n",
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}",
			),
			array(
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\nNon-template text\n{{tpl}}\n",
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}",
			),
		);
	}
}
