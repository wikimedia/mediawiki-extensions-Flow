<?php

namespace Flow\Tests\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Container;
use Flow\Conversion\Utils;
use Flow\Exception\WikitextException;
use Flow\Import\IObjectRevision;
use Flow\Import\Wikitext\ImportSource;
use Parser;
use Title;
use WikitextContent;

/**
 * @covers \Flow\Import\Wikitext\ImportSource
 *
 * @group Flow
 * @group Database
 */
class ImportSourceTest extends \MediaWikiIntegrationTestCase {

	/** @inheritDoc */
	protected $tablesUsed = [ 'page', 'revision', 'ip_changes' ];

	protected function setUp(): void {
		// https://gerrit.wikimedia.org/r/c/mediawiki/extensions/Flow/+/927619 needs to be merged
		// but until then, skip this test unconditionally.
		// TODO: remove the below line once the above patch is merged.
		$this->markTestSkipped( 'Until Ifb85f4733be3b43b71b111df0cd3d88281101153 gets merged' );

		parent::setUp();

		// Check for Parsoid
		try {
			Utils::convert( 'html', 'wikitext', 'Foo', Title::makeTitle( NS_MAIN, 'ImportSourceTest' ) );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
	}

	/**
	 * @dataProvider getHeaderProvider
	 */
	public function testGetHeader( $content, $expectText ) {
		$user = Container::get( 'occupation_controller' )->getTalkpageManager();

		// create a page with some content
		$status = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( Title::newMainPage() )
			->doUserEditContent(
				new WikitextContent( $content ),
				$this->getTestUser()->getUser(),
				"and an edit summary"
			);
		if ( !$status->isGood() ) {
			$this->fail( $status->getMessage()->plain() );
		}

		$source = new ImportSource(
			Title::newMainPage(),
			$this->createMock( Parser::class ),
			$user
		);

		$header = $source->getHeader();
		$this->assertNotNull( $header );
		$this->assertGreaterThan( 1, strlen( $header->getObjectKey() ) );

		$revisions = iterator_to_array( $header->getRevisions() );
		$this->assertCount( 1, $revisions );

		$revision = reset( $revisions );
		$this->assertInstanceOf( IObjectRevision::class, $revision );
		$this->assertEquals( $expectText, $revision->getText() );
		$this->assertEquals( $user->getName(), $revision->getAuthor() );
	}

	public static function getHeaderProvider() {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$date = $now->format( 'Y-m-d' );

		return [
			[
				// original page content
				"This is some content\n",
				// content to be stored to header
				"\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"{{tpl}}\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"{{tpl}}\nNon-template text\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"Non-template text\n{{tpl}}\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"Non-template text\n{{tpl}}\nNon-template text\n",
				"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"{{tpl}}\nNon-template text\n{{tpl}}\nNon-template text\n{{tpl}}\n",
				"{{tpl}}\n{{tpl}}\n{{tpl}}\n\n" .
					"{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"{{tpl\n|key=value}}\n",
				"{{tpl\n|key=value}}\n\n" .
					"{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}"
			],
			[
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n",
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n\n" .
					"{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}",
			],
			[
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\nNon-template text\n{{tpl}}\n",
				"{{multiple issues|\n{{copyedit}}\n{{cleanup tone}}\n}}\n" .
					"{{tpl}}\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}",
			],
		];
	}
}
