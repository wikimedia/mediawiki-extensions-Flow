<?php

namespace Flow\Tests\Import;

use Flow\Import\NullImportSourceStore;
use Flow\Import\PageImportState;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Psr\Log\NullLogger;
use SplQueue;
use Title;
use User;

/**
 * @group Flow
 */
class PageImportStateTest extends \MediaWikiTestCase {

	protected function createState( $returnAll = false ) {
		$storage = $this->getMockBuilder( 'Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		$workflow = Workflow::create(
			'discussion',
			Title::newMainPage()
		);

		$state = new PageImportState(
			$workflow,
			$storage,
			new NullImportSourceStore(),
			new NullLogger(),
			$this->getMockBuilder( 'Flow\Data\BufferedCache' )
				->disableOriginalConstructor()
				->getMock(),
			$this->getMockBuilder( 'Flow\DbFactory' )
				->disableOriginalConstructor()
				->getMock(),
			new ProcessorGroup,
			new SplQueue
		);
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

	public function testSetsOnlyRevIdByTimestampForTopicTitle() {
		$state = $this->createState();
		$topicWorkflow = Workflow::create(
			'topic',
			Title::newMainPage()
		);
		$topicTitle = PostRevision::create(
			$topicWorkflow,
			User::newFromName( '127.0.0.1', false ),
			'sing song',
			'wikitext'
		);

		$now = time();
		$state->setRevisionTimestamp( $topicTitle, $now - 54321 );
		$this->assertTrue(
			$topicTitle->getPostId()->equals( $topicWorkflow->getId() ),
			'Topic title postId must still match workflow id'
		);
		$this->assertEquals(
			$now - 54321,
			$topicTitle->getRevisionId()->getTimestampObj()->getTimestamp( TS_UNIX )
		);
	}

	public function testSetsRevIdAndPostIdForReplys() {
		$state = $this->createState();
		$user = User::newFromName( '127.0.0.1', false );
		$title = Title::newMainPage();
		$topicWorkflow = Workflow::create( 'topic', $title );
		$topicTitle = PostRevision::create( $topicWorkflow, $user, 'sing song', 'wikitext' );
		$reply = $topicTitle->reply( $topicWorkflow, $user, 'fantastic!', 'wikitext' );

		$now = time();

		$state->setRevisionTimestamp( $reply, $now - 54321 );
		$this->assertEquals(
			$now - 54321,
			$reply->getRevisionId()->getTimestampObj()->getTimestamp( TS_UNIX ),
			'The first reply revision must have its revision id set appropriatly'
		);
		$this->assertTrue(
			$reply->getPostId()->equals( $reply->getRevisionId() ),
			'The first revision of a reply shares its postId and revId'
		);
	}
}
