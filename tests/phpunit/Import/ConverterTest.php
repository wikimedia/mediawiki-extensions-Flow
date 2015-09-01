<?php

namespace Flow\Tests\Import;

use DatabaseBase;
use Flow\Import\Converter;
use Flow\Import\IConversionStrategy;
use Flow\Import\Importer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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

	protected function createConverter(
		DatabaseBase $dbw = null,
		Importer $importer = null,
		LoggerInterface $logger = null,
		User $user = null,
		IConversionStrategy $strategy = null
	) {
		return new Converter(
			$dbw ?: wfGetDB( DB_MASTER ),
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
