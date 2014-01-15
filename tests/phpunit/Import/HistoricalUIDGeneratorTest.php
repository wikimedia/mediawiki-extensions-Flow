<?php

namespace Flow\Tests\Import;

use Flow\Import\HistoricalUIDGenerator;
use Flow\Model\UUID;

/**
 * @group Flow
 */
class HistoricalUIDGeneratorTest extends \MediaWikiTestCase {

	public function roundTripProvider() {
		$now = time();

		return array(
			array( $now - 86400 ),
			array( $now - ( 365 * 86400 ) ),
		);
	}

	/**
	 * @dataProvider roundTripProvider
	 */
	public function testRoundTrip( $timestamp ) {
		$timestamp = wfTimestamp( TS_UNIX, $timestamp );
		$uid = HistoricalUIDGenerator::historicalTimestampedUID88( $timestamp );
		$uuid = UUID::create( $uid );

		$returned = $uuid->getTimestampObj()->getTimestamp( TS_UNIX );
		$this->assertEquals( $timestamp, $returned );
	}
}
