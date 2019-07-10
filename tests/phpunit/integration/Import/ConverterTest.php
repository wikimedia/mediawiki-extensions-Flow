<?php

namespace Flow\Tests\Import;

use Wikimedia\Rdbms\IDatabase;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\Importer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use User;

/**
 * @covers \Flow\Import\Converter
 *
 * @group Flow
 */
class ConverterTest extends \MediaWikiTestCase {
	public function testConstruction() {
		$this->assertInstanceOf(
			Converter::class,
			$this->createConverter()
		);
	}

	protected function createConverter(
		IDatabase $dbw = null,
		Importer $importer = null,
		LoggerInterface $logger = null,
		User $user = null,
		IConversionStrategy $strategy = null
	) {
		return new Converter(
			$dbw ?: wfGetDB( DB_MASTER ),
			$importer ?: $this->getMockBuilder( Importer::class )
				->disableOriginalConstructor()
				->getMock(),
			$logger ?: new NullLogger,
			$user ?: User::newFromId( 1 ),
			$strategy ?: $this->getMockBuilder( IConversionStrategy::class )
				->disableOriginalConstructor()
				->getMock()
		);
	}
}
