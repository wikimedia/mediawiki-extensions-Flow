<?php

namespace Flow\Tests\Import;

use Flow\Container;
use Flow\Import\NullImportSourceStore;
use Flow\Import\PageImportState;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Import\TalkpageImportOperation;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;
use Flow\Tests\Mock\MockImportHeader;
use Flow\Tests\Mock\MockImportPost;
use Flow\Tests\Mock\MockImportRevision;
use Flow\Tests\Mock\MockImportSource;
use Flow\Tests\Mock\MockImportSummary;
use Flow\Tests\Mock\MockImportTopic;
use Psr\Log\NullLogger;
use SplQueue;
use Title;
use User;

/**
 * @group Flow
 */
class TalkpageImportOperationTest extends \MediaWikiTestCase {

	/**
	 * This is a horrible test, it basically runs the whole thing
	 * and sees if it falls over.
	 */
	public function testImportDoesntCompletelyFail() {
		$workflow = Workflow::create(
			'discussion',
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
		$storage->expects( $this->any() )
			->method( 'multiPut' )
			->will( $this->returnCallback( function( $objs ) use( &$stored ) {
				$stored = array_merge( $stored, $objs );
			} ) );

		$now = time();
		$source = new MockImportSource(
			new MockImportHeader( array(
				// header revisions
				new MockImportRevision( array( 'createdTimestamp' => $now ) ),
			) ),
			array(
				new MockImportTopic(
					new MockImportSummary( array(
						new MockImportRevision( array( 'createdTimestamp' => $now - 250 ) ),
					) ),
					array(
						// topic title revisions
						new MockImportRevision( array( 'createdTimestamp' => $now - 1000 ) ),
					),
					array(
						//replies
						new MockImportPost(
							array(
								// revisions
								new MockImportRevision( array( 'createdTimestmap' => $now - 1000 ) ),
							),
							array(
								// replies
								new MockImportPost(
									array(
										// revisions
										new MockImportRevision( array(
											'createdTimestmap' => $now - 500,
											'user' => User::newFromNAme( '10.0.0.2', false ),
										) ),
									),
									array(
										// replies
									)
								),
							)
						),
					)
				)
			)
		);

		$op = new TalkpageImportOperation( $source );
		$store = new NullImportSourceStore;
		$op->import( new PageImportState(
			$workflow,
			$storage,
			$store,
			new NullLogger(),
			$this->getMockBuilder( 'Flow\Data\BufferedCache' )
				->disableOriginalConstructor()
				->getMock(),
			Container::get( 'db.factory' ),
			new ProcessorGroup,
			new SplQueue
		) );

		// Count what actually came through
		$storedHeader = $storedDiscussion = $storedTopics = $storedTopicListEntry = $storedSummary = $storedPosts = 0;
		foreach ( $stored as $obj ) {
			if ( $obj instanceof Workflow ) {
				if ( $obj->getType() === 'discussion' ) {
					$this->assertSame( $workflow, $obj );
					$storedDiscussion++;
				} else {
					$alpha = $obj->getId()->getAlphadecimal();
					if ( !isset( $seenWorkflow[$alpha] ) ) {
						$seenWorkflow[$alpha] = true;
						$this->assertEquals( 'topic', $obj->getType() );
						$storedTopics++;
						$topicWorkflow = $obj;
					}
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
			} elseif ( $obj instanceof Header ) {
				$storedHeader++;
			} else {
				$this->fail( 'Unexpected object stored:' . get_class( $obj ) );
			}
		}

		// Verify we wrote the expected objects to storage

		$this->assertEquals( 1, $storedHeader );

		$this->assertEquals( 1, $storedDiscussion );
		$this->assertEquals( 1, $storedTopics );
		$this->assertEquals( 1, $storedTopicListEntry );
		$this->assertEquals( 1, $storedSummary );
		$this->assertEquals( 3, $storedPosts );

		// This total expected number of insertions should match the sum of the left assertEquals parameters above.
		$this->assertCount( 8, array_unique( array_map( 'spl_object_hash', $stored ) ) );

		// Other special cases we need to check
		$this->assertTrue(
			$topicTitle->getPostId()->equals( $topicWorkflow->getId() ),
			'Root post id must match its workflow'
		);
	}
}
