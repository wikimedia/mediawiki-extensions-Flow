<?php

namespace Flow\Tests\Import;

use Flow\Import\PageImportState;
use Flow\Import\TalkpageImportOperation;
use Flow\Model\Workflow;
use Flow\Tests\Mock\MockImportPost;
use Flow\Tests\Mock\MockImportSource;
use Flow\Tests\Mock\MockImportSummary;
use Flow\Tests\Mock\MockImportTopic;
use Title;
use User;

/**
 * @group Flow
 */
class TalkpageImportOperationTest extends \MediaWikiTestCase {

	/**
	 * This is the most basic of basic tests, just kinda runs it
	 * and sees if it falls over.
	 *
	 * @todo write better tests
	 */
	public function testImportDoesntCompletelyFail() {
		$workflow = Workflow::create(
			'discussion',
			User::newFromName( '127.0.0.1', false ),
			Title::newMainPage()
		);
		$storage = $this->getMockBuilder( 'Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		$now = time();
		$source = new MockImportSource( array(
			new MockImportTopic( array(
				'createdTimestamp' => $now - 1000,
				'summary' => new MockImportSummary(),
				'replies' => array(
					new MockImportPost( array(
						'createdTimestamp' => $now - 1000,
						'replies' => array(
							new MockImportPost( array(
								'createdTimestmap' => $now - 500,
								'user' => User::newFromName( '10.0.0.2', false ),
							) ),
						),
					) ),
				),
			) ),
		) );

		$op = new TalkpageImportOperation( $source );
		$op->import( new PageImportState( $workflow, $storage ) );
		$this->assertTrue( true );
	}
}
