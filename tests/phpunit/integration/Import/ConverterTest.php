<?php

namespace Flow\Tests\Import;

use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\Importer;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wikimedia\Rdbms\IDatabase;

/**
 * @covers \Flow\Import\Converter
 *
 * @group Flow
 */
class ConverterTest extends \MediaWikiIntegrationTestCase {
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
			$dbw ?: $this->createMock( IDatabase::class ),
			$importer ?: $this->createMock( Importer::class ),
			$logger ?: new NullLogger,
			$user ?: User::newFromId( 1 ),
			$strategy ?: $this->createMock( IConversionStrategy::class )
		);
	}
}
