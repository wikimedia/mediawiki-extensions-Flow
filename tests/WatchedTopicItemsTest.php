<?php

namespace Flow\Tests;

use Flow\Model\UUID;
use Flow\WatchedTopicItems;
use User;

/**
 * @group Flow
 */
class WatchedTopicItemTest extends FlowTestCase {

	public function provideDataGetWatchStatus() {
		// number of test cases
		$testCount = 10;
		$tests = array();
		while ( $testCount > 0 ) {
			$testCount--;
			// number of uuid per test case
			$uuidCount = 10;
			$uuids = $dbResult = $result = array();
			while( $uuidCount > 0 ) {
				$uuidCount--;
				$uuid = UUID::create()->getAlphadecimal();
				$rand = rand( 0, 1 );
				// put in the query result
				if ( $rand ) {
					$dbResult[] = ( object )array( 'wl_title' => $uuid );
					$result[$uuid] = true;
				} else {
					$result[$uuid] = false;
				}
				$uuids[] = $uuid;
			}
			$dbResult = new \ArrayObject( $dbResult );
			$tests[] = array( $uuids, $dbResult->getIterator(), $result );
		}

		// attach empty uuids array to query
		$uuids = $dbResult = $result = array();
		$emptyCount = 10;
		while ( $emptyCount > 0 ) {
			$emptyCount--;
			$uuid = UUID::create()->getAlphadecimal();
			$dbResult[] = ( object )array( 'wl_title' => $uuid );
		}
		$dbResult = new \ArrayObject( $dbResult );
		$tests[] = array( $uuids, $dbResult->getIterator(), $result );
		return $tests;
	}

	/**
	 * @dataProvider provideDataGetWatchStatus
	 *
	 */
	public function testGetWatchStatus( $uuids, $dbResult, $result ) {
		// give it a fake user id
		$watchedTopicItems = new WatchedTopicItems( User::newFromId( 1 ), $this->mockDb( $dbResult ) );
		$res = $watchedTopicItems->getWatchStatus( $uuids );
		$this->assertEquals( count( $res ), count( $result ) );
		foreach ( $res as $key => $value ) {
			$this->assertArrayHasKey( $key, $result );
			$this->assertEquals( $value, $result[$key] );
		}

		// false values for all uuids for anon users
		$watchedTopicItems = new WatchedTopicItems( User::newFromId( 0 ), $this->mockDb( $dbResult ) );
		foreach ( $watchedTopicItems->getWatchStatus( $uuids ) as $value ) {
			$this->assertFalse( $value );
		}
	}

	protected function mockDb( $dbResult ) {
		$db = $this->getMockBuilder( '\DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();
		$db->expects( $this->any() )
			->method( 'select' )
			->will( $this->returnValue( $dbResult ) );
		return $db;
	}
}
