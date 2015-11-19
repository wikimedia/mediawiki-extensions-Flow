<?php

namespace Flow\Tests\SpamFilter;

use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\SpamFilter\ContentLengthFilter;
use User;
use Title;

/**
 * @group Flow
 */
class ContentLengthFilterTest extends \MediaWikiTestCase {
	/**
	 * @var SpamRegex
	 */
	protected $spamFilter;

	public function spamProvider() {
		return array(
			array(
				'With content shorter than max length allow through filter',
				// expect
				true,
				// content
				'blah',
				// max length
				100
			),

			array(
				'With content longer than max length dissalow through filter',
				// expect
				false,
				// content
				'blah',
				// max length
				2
			),
		);
	}

	/**
	 * @dataProvider spamProvider
	 */
	public function testSpam( $message, $expect, $content, $maxLength ) {
		$title = Title::newFromText( 'UTPage' );
		$user = User::newFromName( '127.0.0.1', false );
		$workflow = Workflow::create( 'topic', $title );
		$topic = PostRevision::createTopicPost( $workflow, $user, 'title content' );
		$reply = $topic->reply( $workflow, $user, $content, 'wikitext' );

		$spamFilter = new ContentLengthFilter( $maxLength );
		$status = $spamFilter->validate( $this->getMock( 'IContextSource' ), $reply, null, $title );
		$this->assertEquals( $expect, $status->isOK() );
	}
}
