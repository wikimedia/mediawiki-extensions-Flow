<?php

namespace Flow\Tests\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Import\Wikitext\ImportSource;
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

	public function testGetHeader() {
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$date = $now->format( 'Y-m-d' );

		// create a page with some content
		$status = WikiPage::factory( Title::newMainPage() )
			->doEditContent(
				new WikitextContent( "This is some content\n" ),
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
		$this->assertEquals(
			"This is some content\n\n{{Wikitext talk page converted to Flow|archive=Main Page|date=$date}}",
			$revision->getText()
		);
	}
}
