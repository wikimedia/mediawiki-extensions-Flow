<?php

/**
 * @database
 * @Flow
 */
class SomethingTest extends MediaWikiTestCase {

	public function testSomething() {
		$loader = Flow\Container::get( 'factory.loader.workflow' )
			->createWorkflowLoader( Title::newFromText( 'Talk:Flow QA' ) );

		$blocks = $loader->createBlocks();
		$user = User::newFromName( 'UTAdmin' );
		foreach ( $blocks as $block ) {
			$block->init( 'new-topic', $user );
		}
		$blocksToCommit = $loader->handleSubmit( 'new-topic', $blocks, $user, array(
			'topiclist' => array(
				'topic' => 'This is the title of the topic',
				'content' => 'And this is some content',
			),
		) );

		$this->assertEquals( 1, count( $blocksToCommit ), 'Submission did not fail' );
		$loader->commit( $loader->getWorkflow(), $blocksToCommit );
		$this->assertTrue( true, 'foo?' );
	}

}
