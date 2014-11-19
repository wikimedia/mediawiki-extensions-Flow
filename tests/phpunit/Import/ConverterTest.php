<?php

namespace Flow\Tests\Import;

use DatabaseBase;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\Importer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Title;
use User;

/**
 * @group Flow
 */
class ConverterTest extends \MediaWikiTestCase {
	public function testConstruction() {
		$this->assertInstanceOf(
			'Flow\Import\Converter',
			$this->createConverter()
		);
	}

	public function decideArchiveTitleProvider() {
		return array(
			array(
				'Selects the first pattern if n=1 does exist',
				// expect
				'Talk:Flow/Archive 1',
				// source title
				Title::newFromText( 'Talk:Flow' ),
				// formats
				array( '%s/Archive %d', '%s/Archive%d' ),
				// existing titles
				array(),
			),

			array(
				'Selects n=2 when n=1 exists',
				// expect
				'Talk:Flow/Archive 2',
				// source title
				Title::newFromText( 'Talk:Flow' ),
				// formats
				array( '%s/Archive %d' ),
				// existing titles
				array( 'Talk:Flow/Archive 1' ),
			),

			array(
				'Selects the second pattern if n=1 exists',
				// expect
				'Talk:Flow/Archive2',
				// source title
				Title::newFromText( 'Talk:Flow' ),
				// formats
				array( '%s/Archive %d', '%s/Archive%d' ),
				// existing titles
				array( 'Talk:Flow/Archive1' ),
			),
		);
	}
	/**
	 * @dataProvider decideArchiveTitleProvider
	 */
	public function testDecideArchiveTitle( $message, $expect, Title $source, array $formats, array $exists ) {
		// flip so we can use isset
		$existsByKey = array_flip( $exists );

		$titleRepo = $this->getMock( 'Flow\Repository\TitleRepository' );
		$titleRepo->expects( $this->any() )
			->method( 'exists' )
			->will( $this->returnCallback( function( Title $title ) use ( $existsByKey ) {
				return isset( $existsByKey[$title->getPrefixedText()] );
			} ) );

		$result = Converter::decideArchiveTitle( $source, $formats, $titleRepo );
		$this->assertEquals( $expect, $result, $message );
	}

	protected function createConverter(
		DatabaseBase $dbr = null,
		Importer $importer = null,
		LoggerInterface $logger = null,
		User $user = null,
		IConversionStrategy $strategy = null
	) {
		return new Converter(
			$dbr ?: wfGetDB( DB_SLAVE ),
			$importer ?: $this->getMockBuilder( 'Flow\Import\Importer' )
				->disableOriginalConstructor()
				->getMock(),
			$logger ?: new NullLogger,
			$user ?: User::newFromId( 1 ),
			$strategy ?: $this->getMockBuilder( 'Flow\Import\IConversionStrategy' )
				->disableOriginalConstructor()
				->getMock()
		);
	}
}
