<?php

namespace Flow\Tests;

use MWTimestamp;
use Flow\Container;
use Flow\Model\UUID;
use Flow\View\History;

class HistoryTest extends PostRevisionTestCase {

	static public function recordGroupingProvider() {
		// feb 11 2014, 12:00:00
		$now = new MWTimestamp( '20140211190000');
		$now = (int)$now->getTimestamp( TS_UNIX );

		return array(
			array(
				'Older entries are grouped by month',
				array(
					'February 2014' => 1,
					'January 2014' => 3,
					'December 2013' => 1,
				),
				$now,
				array(
					'20140201000000',
					'20140125000000',
					'20140115000000',
					'20140105000000',
					'20131228000000',
				),
			),

			array(
				'bugged records from the future are a member of last 4 hours',
				array(
					'Last 4 hours' => 1,
				),
				$now,
				array(
					wfTimestamp( TS_MW, $now + ( 60 * 60 * 24 * 7 ) )
				),
			),

			array(
				'new records are a member of last 4 hours',
				array(
					'Last 4 hours' => 1,
				),
				$now,
				array(
					wfTimestamp( TS_MW, $now ),
				),
			),

			array(
				'3.5 hours ago is a member of last 4 hours',
				array(
					'Last 4 hours' => 1,
				),
				$now,
				array(
					wfTimestamp( TS_MW, $now - (int)( 60 * 60 * 3.5 ) ),
				),
			),

			array(
				'5 hours ago is a member of last week if its the previous day',
				array(
					'Last week' => 1,
				),
				// 2014 feb 11 03:00:00
				new MWTimestamp( '20140211030000' ),
				array(
					// 2014 feb 10 22:00:00
					new MWTimestamp( '20140210220000' ),
				),
			),

			array(
				'5 hours ago is a member of today if its the same day',
				array(
					'Today' => 1,
				),
				// 2014 feb 11 03:00:00
				new MWTimestamp( '20140211080000' ),
				array(
					// 2014 feb 10 22:00:00
					new MWTimestamp( '20140211030000' ),
				),
			),
		);
	}

	/**
	 * @dataProvider recordGroupingProvider
	 */
	public function testRecordGrouping( $message, array $expected, $now, array $timestamps ) {
		// meh
		$this->setMwGlobals( 'wgTitle', \Title::newMainPage() );

		$postId = UUID::create();
		$records = array();
		foreach ( $timestamps as $timestamp ) {
			$records[] = $this->mockPostRevision( $postId, $timestamp );
		}

		$block = $this->getMock( 'Flow\Block\Block' );
		$block->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( 'mockblock' ) );
		$block->expects( $this->any() )
			->method( 'getWorkflowId' )
			->will( $this->returnValue( $postId ) ); // wrong, but good enough

		$history = new History\History( $records );
		// We need a slightly tweaked version of HistoryRenderer that doesn't talk to storage
		$rend = $this->getMockBuilder( 'Flow\View\History\HistoryRenderer' )
			->setConstructorArgs( array( Container::get( 'permissions' ), Container::get( 'templating' ), Container::get( 'repository.username' ), $block, $now ) )
			->setMethods( array( 'batchLoadWorkflow' ) )
			->getMock();

		$res = array();
		foreach ( $rend->getTimespans( $history ) as $text => $timespan ) {
			$timespan = $history->getTimespan( $timespan['from'], $timespan['to'] );
			// empty results for timespans we don't care about are expected
			if ( $timespan->numRows() > 0 || isset( $expected[$text] ) ) {
				$res[$text] = $timespan->numRows();
			}
		}

		// All expected timespans must be found in the above foreach
		$this->assertEquals( $expected, $res, $message );
	}

	protected function mockPostRevision( UUID $postId, $timestamp ) {
		$revId = UUID::getComparisonUUID( $timestamp );
		return $this->generateObject( array(
			'rev_id' => $revId->getBinary(),
			'tree_rev_id' =>  $revId->getBinary(),
			'tree_rev_descendant_id' => $postId->getBinary(),
			'rev_type_id' => $postId->getBinary(),
		), array(), 1 );
	}
}
