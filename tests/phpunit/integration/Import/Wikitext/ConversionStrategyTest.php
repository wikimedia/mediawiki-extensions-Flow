<?php

namespace Flow\Tests\Import\Wikitext;

use DateTime;
use DateTimeZone;
use Flow\Container;
use Flow\Hooks;
use Flow\Import\IImportSource;
use Flow\Import\SourceStore\NullImportSourceStore;
use Flow\Import\SourceStore\SourceStoreInterface;
use Flow\Import\Wikitext\ConversionStrategy;
use LinkCacheTestTrait;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * @covers \Flow\Import\Wikitext\ConversionStrategy
 *
 * @group Flow
 * @group Database
 */
class ConversionStrategyTest extends MediaWikiIntegrationTestCase {
	use LinkCacheTestTrait;

	protected function setUp(): void {
		parent::setUp();
		Container::reset();
		Hooks::resetFlowExtension();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ConversionStrategy::class,
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
			IImportSource::class,
			$this->createStrategy()->createImportSource( Title::newFromText( 'Talk:Blue_birds' ) )
		);
	}

	public function testReturnsValidSourceStore() {
		$this->assertInstanceOf(
			SourceStoreInterface::class,
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
		$this->assertInstanceOf( WikitextContent::class, $result );
		$this->assertEquals(
			"{{Archive for converted wikitext talk page|from=Talk:Blue birds|date=$date}}\n\nFour score and...",
			$result->getText()
		);
	}

	/**
	 * @group Broken
	 */
	public function testShouldConvertLqt() {
		$this->markTestSkippedIfExtensionNotLoaded( 'Liquid Threads' );

		$strategy = $this->createStrategy();

		$lqtPagesName = 'Talk:Some ConversionStrategyTest LQT page';
		$this->overrideConfigValues( [
			'LqtNamespaces' => [ NS_HELP_TALK ],
			'LqtPages' => [ $lqtPagesName ],
		] );

		// Not subpage, not LQT
		$nonLqtTitle = Title::newFromText( 'Talk:Some ConversionStrategyTest page' );
		$this->assertTrue(
			$strategy->shouldConvert( $nonLqtTitle ),
			'Normal non-LQT talk page should be converted'
		);

		$lqtNamespacesTitle = Title::makeTitle(
			NS_HELP_TALK,
			'Some other ConversionStrategyTest LQT page'
		);
		$this->assertFalse(
			$strategy->shouldConvert( $lqtNamespacesTitle ),
			'LQT wgLqtNamespaces talk page should not be converted'
		);

		$lqtPagesTitle = Title::newFromText( $lqtPagesName );
		$this->assertFalse(
			$strategy->shouldConvert( $lqtPagesTitle ),
			'LQT wgLqtPages talk page should not be converted'
		);
	}

	/**
	 * @dataProvider provideMeetsSubpageRequirements
	 */
	public function testMeetsSubpageRequirements( $pageName, $expectedResult, $subjectExists, $message ) {
		$strategy = $this->createStrategy();
		$title = Title::newFromText( $pageName );
		$subjectTitle = $this->getServiceContainer()->getNamespaceInfo()->getSubjectPage( $title );
		$linkCache = $this->getServiceContainer()->getLinkCache();

		// Fake whether $subjectTitle exists
		if ( $subjectExists ) {
			$this->addGoodLinkObject(
				1, // Fake article ID
				$subjectTitle
			);
		} else {
			$linkCache->addBadLinkObj( $subjectTitle );
		}

		$this->assertSame(
			$expectedResult,
			$strategy->meetsSubpageRequirements( $title ),
			$message
		);
	}

	public static function provideMeetsSubpageRequirements() {
		return [
			[
				'Talk:Some ConversionStrategyTest page',
				true,
				true, // Shouldn't matter
				'Non-subpage talk page',
			],
			[
				'Talk:Some/ConversionStrategyTest subpage 1',
				true,
				true,
				'Talk subpage where subject exists',
			],
			[
				'Talk:Some/ConversionStrategyTest subpage 2',
				false,
				false,
				'Talk subpage where subject doesn\'t exist',
			],
			[
				'User:Some/ConversionStrategyTest subpage',
				false,
				true,
				'Existing subpage in subject namespace'
			],
		];
	}

	protected function createStrategy(
		?Parser $parser = null,
		?SourceStoreInterface $sourceStore = null
	) {
		return new ConversionStrategy(
			$parser ?: $this->getServiceContainer()->getParser(),
			$sourceStore ?: new NullImportSourceStore,
			Container::get( 'default_logger' ),
			Container::get( 'occupation_controller' )->getTalkpageManager()
		);
	}
}
