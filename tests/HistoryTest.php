<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\UUID;
use Flow\View\History;

class HistoryTest extends PostRevisionTestCase {

	public function testSomething() {
		// meh
		$this->setMwGlobals( 'wgTitle', \Title::newMainPage() );

		$postId = UUID::create();
		$records = array(
			$this->mockPostRevision( $postId, '20140205000000' ),
			$this->mockPostRevision( $postId, '20140125000000' ),
			$this->mockPostRevision( $postId, '20140115000000' ),
			$this->mockPostRevision( $postId, '20140105000000' ),
			$this->mockPostRevision( $postId, '20131228000000' ),
		);

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
			->setConstructorArgs( array( Container::get( 'templating' ), $block ) )
			->setMethods( array( 'batchLoadWorkflow' ) )
			->getMock();

		$found = 0;
		foreach ( $rend->getTimespans( $history ) as $text => $timespan ) {
			$timespan = $history->getTimespan( $timespan['from'], $timespan['to'] );
			$found += $timespan->numRows();
		}
		$this->assertEquals( count( $records ), $found );
	}

	protected function mockPostRevision( UUID $postId, $timestamp ) {
		$revId = UUID::getComparisonUUID( $timestamp );
		return $this->generateObject( array(
			'rev_id' => $revId->getBinary(),
			'tree_rev_id' =>  $revId->getBinary(),
			'tree_rev_descendant_id' => $postId->getBinary(),
		), array(), 1 );

	}
}
