<?php

namespace Flow\Tests\Import\LiquidThreadsApi;

use DateTime;
use DateTimeZone;
use ExtensionRegistry;
use Flow\Import\IImportSource;
use Flow\Import\LiquidThreadsApi\ApiBackend;
use Flow\Import\LiquidThreadsApi\ConversionStrategy;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\SourceStore\NullImportSourceStore;
use Flow\Import\SourceStore\SourceStoreInterface;
use Flow\Notifications\Controller;
use Flow\UrlGenerator;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use Wikimedia\Rdbms\IDatabase;
use WikitextContent;

/**
 * @covers \Flow\Import\LiquidThreadsApi\ConversionStrategy
 *
 * @group Flow
 */
class ConversionStrategyTest extends \MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		// Stash existing $wgEchoNotifications and provide a dummy for these
		// tests:  LqtNotifications::overrideUsersToNotify will override it
		global $wgEchoNotifications;
		$this->setMwGlobals( 'wgEchoNotifications', $wgEchoNotifications );
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
		$titleFactory = $this->createMock( TitleFactory::class );
		$titleFactory->method( 'newFromText' )->willReturnCallback( static function () {
			$ret = Title::newFromText( ...func_get_args() );
			// Mark the page as nonexisting, so that we get format = 0 n = 1
			$ret->resetArticleID( 0 );
			return $ret;
		} );
		$this->setService( 'TitleFactory', $titleFactory );

		$titleText = 'TestDecidesArchiveTitle';
		$this->assertEquals(
			"Talk:$titleText/LQT Archive 1",
			$this->createStrategy()
				->decideArchiveTitle( Title::makeTitle( NS_TALK, $titleText ) )
				->getPrefixedText()
		);
	}

	public static function provideArchiveCleanupRevisionContent() {
		// @todo superm401 suggested finding library that lets us control time during tests,
		// would probably be better
		$now = new DateTime( "now", new DateTimeZone( "GMT" ) );
		$date = $now->format( 'Y-m-d' );

		return [
			[
				'Blank input page',
				// expect
				"{{Archive for converted LQT page|from=Talk:Blue birds|date=$date}}\n\n{{#useliquidthreads:0}}\n\n",
				// input content
				'',
			],
			[
				'Page containing lqt magic word',
				// expect
				"{{Archive for converted LQT page|from=Talk:Blue birds|date=$date}}\n\n{{#useliquidthreads:0}}\n\n",
				// input content
				'{{#useliquidthreads:1}}',
			],

			[
				'Page containing some stuff and the lqt magic word',
				// expect
				<<<EOD
{{Archive for converted LQT page|from=Talk:Blue birds|date=$date}}

{{#useliquidthreads:0}}

Four score and seven years ago our fathers brought forth
on this continent, a new nation, conceived in Liberty, and
dedicated to the proposition that all men are created equal.

EOD
				,
				// input content
				<<<EOD
Four score and seven years ago our fathers brought forth
on this continent, a new nation, conceived in Liberty, and
dedicated to the proposition that all men are created equal.
{{#useliquidthreads:
	1
}}
EOD
			],
		];
	}

	/**
	 * @group Broken
	 * @dataProvider provideArchiveCleanupRevisionContent
	 */
	public function testCreateArchiveCleanupRevisionContent( $message, $expect, $content ) {
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Liquid Threads' ) ) {
			$this->markTestSkipped( 'LiquidThreads not enabled' );
		}

		$result = $this->createStrategy()->createArchiveCleanupRevisionContent(
			new WikitextContent( $content ),
			Title::newFromText( 'Talk:Blue_birds' )
		);
		if ( $result !== null ) {
			$this->assertInstanceOf( WikitextContent::class, $result );
		}
		$this->assertEquals( $expect, $result->getText(), $message );
	}

	public function testGetPostprocessor() {
		$this->assertInstanceOf(
			Postprocessor::class,
			$this->createStrategy()->getPostprocessor()
		);
	}

	private function createStrategy() {
		return new ConversionStrategy(
			$this->createMock( IDatabase::class ),
			new NullImportSourceStore(),
			$this->createMock( ApiBackend::class ),
			$this->createMock( UrlGenerator::class ),
			$this->createMock( User::class ),
			$this->createMock( Controller::class )
		);
	}
}
