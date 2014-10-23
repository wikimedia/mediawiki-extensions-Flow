<?php

namespace Flow\Tests\Import;

use Flow\Import\PageImportState;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Title;
use User;

class PageImportStateTest extends \MediaWikiTestCase {

	protected function createState( $returnAll = false ) {
		$storage = $this->getMockBuilder( 'Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		$workflow = Workflow::create(
			'discussion',
			User::newFromName( '127.0.0.1', false ),
			Title::newMainPage()
		);

		$state = new PageImportState( $workflow, $storage );
		if ( $returnAll ) {
			return array( $state, $workflow, $storage );
		} else {
			return $state;
		}
	}

	public function testGetTimestampIdReturnsUUID() {
		$state = $this->createState();
		$this->assertInstanceOf(
			'Flow\Model\UUID',
			$state->getTimestampId( time() - 123456 ),
			'PageImportState::getTimestampId must return a UUID object'
		);
	}

	public function testSetsWorkflowIdByTimestamp() {
		list( $state, $workflow ) = $this->createState( true );
		$now = time();
		$state->setWorkflowTimestamp( $workflow, $now - 123456 );
		$this->assertEquals(
			$now - 123456,
			$workflow->getId()->getTimestampObj()->getTimestamp( TS_UNIX )
		);
	}

	public function testSetsPostIdsByTimestamp() {
		$state = $this->createState();
		$topicWorkflow = Workflow::create(
			'topic',
			User::newFromName( '127.0.0.1', false ),
			Title::newMainPage()
		);
		$topicTitle = PostRevision::create( $topicWorkflow, 'sing song' );

		$now = time();
		$state->setPostTimestamp( $topicTitle, $now - 54321 );
		$this->assertEquals(
			$now - 54321,
			$topicTitle->getPostId()->getTimestampObj()->getTimestamp( TS_UNIX )
		);
		$this->assertEquals(
			$now - 54321,
			$topicTitle->getRevisionId()->getTimestampObj()->getTimestamp( TS_UNIX )
		);
	}
}
