<?php

namespace Flow\Tests\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Import\ImportSourceStore;
use Flow\Import\NullImportSourceStore;
use Flow\Import\Wikitext\ConversionStrategy;
use Parser;
use Title;
use WikitextContent;

/**
 * @group Flow
 */
class ConversionStrategyTest extends \MediaWikiTestCase {
	public function testCanConstruct() {
		$this->assertInstanceOf(
			'Flow\Import\Wikitext\ConversionStrategy',
			$this->createStrategy()
		);
	}

	public function testGeneratesMoveComment() {
		$from = Title::newFromText( 'Talk:Blue_birds' );
		$to = Title::newFromText( 'Talk:Blue_birds/Archive 4' );
		$this->assertGreaterThan(
			1,
			strlen( $this->createStrategy()->getMoveComment( $from, $to ) )
		);
	}

	public function testGeneratesCleanupComment() {
		$from = Title::newFromText( 'Talk:Blue_birds' );
		$to = Title::newFromText( 'Talk:Blue_birds/Archive 4' );
		$this->assertGreaterThan(
			1,
			strlen( $this->createStrategy()->getCleanupComment( $from, $to ) )
		);
	}

	public function testCreatesValidImportSource() {
		$this->assertInstanceOf(
			'Flow\Import\IImportSource',
			$this->createStrategy()->createImportSource( Title::newFromText( 'Talk:Blue_birds' ) )
		);
	}

	public function testReturnsValidSourceStore() {
		$this->assertInstanceOf(
			'Flow\Import\ImportSourceStore',
			$this->createStrategy()->getSourceStore()
		);
	}

	public function testDecidesArchiveTitle() {
		// we don't have control of the Title::exists() calls that are made here,
		// so just assume the page doesn't exist and we get format = 0 n = 1
		$this->assertEquals(
			'Talk:Blue birds/Archive 1',
			$this->createStrategy()
				->decideArchiveTitle( Title::newFromText( 'Talk:Blue_birds' ) )
				->getPrefixedText()
		);
	}

	public function testCreateArchiveCleanupRevisionContent() {
		// @todo superm401 suggested finding library that lets us control time during tests,
		// would probably be better
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$date = $now->format( 'Y-m-d' );

		$result = $this->createStrategy()->createArchiveCleanupRevisionContent(
			new WikitextContent( "Four score and..." ),
			Title::newFromText( 'Talk:Blue_birds' )
		);
		$this->assertInstanceOf( 'WikitextContent', $result );
		$this->assertEquals(
			"Four score and...\n\n{{Wikitext talk page converted to Flow|from=Talk:Blue birds|date=$date}}",
			$result->getNativeData()
		);
	}

	protected function createStrategy(
		Parser $parser = null,
		ImportSourceStore $sourceStore = null
	) {
		global $wgParser;

		return new ConversionStrategy(
			$parser ?: $wgParser,
			$sourceStore ?: new NullImportSourceStore
		);
	}
}
