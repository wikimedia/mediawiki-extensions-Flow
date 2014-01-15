<?php

namespace Flow\Tests\Import;

use Flow\Import\PageImportState;
use Flow\Import\TalkpageImportOperation;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
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
	 * This is a horrible test, it basically runs the whole thing
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
		$stored = array();
		$storage->expects( $this->any() )
			->method( 'put' )
			->will( $this->returnCallback( function( $obj ) use( &$stored ) {
				$stored[] = $obj;
			} ) );

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

		// Seven objects should have been inserted
		$this->assertCount( 7, $stored );
		// Count what actually came through
		$storedDiscussion = $storedTopics = $storedTopicListEntry = $storedSummary = $storedPosts = 0;
		foreach ( $stored as $obj ) {
			if ( $obj instanceof Workflow ) {
				if ( $obj->getType() === 'discussion' ) {
					$this->assertSame( $workflow, $obj );
					$storedDiscussion++;
				} else {
					$this->assertEquals( 'topic', $obj->getType() );
					$storedTopics++;
					$topicWorkflow = $obj;
				}
			} elseif ( $obj instanceof PostSummary ) {
				$storedSummary++;
			} elseif ( $obj instanceof PostRevision ) {
				$storedPosts++;
				if ( $obj->isTopicTitle() ) {
					$topicTitle = $obj;
				}
			} elseif ( $obj instanceof TopicListEntry ) {
				$storedTopicListEntry++;
			} else {
				$this->fail( 'Unexpected object stored:' . get_class( $obj ) );
			}
		}

		// Verify we wrote the expected objects to storage
		$this->assertEquals( 1, $storedDiscussion );
		$this->assertEquals( 1, $storedTopics );
		$this->assertEquals( 1, $storedTopicListEntry );
		$this->assertEquals( 1, $storedSummary );
		$this->assertEquals( 3, $storedPosts );

		// Other special cases we need to check
		$this->assertTrue(
			$topicTitle->getPostId()->equals( $topicWorkflow->getId() ),
			'Root post id must match its workflow'
		);
	}
}
