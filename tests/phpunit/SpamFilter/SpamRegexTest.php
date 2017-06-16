<?php

namespace Flow\Tests\SpamFilter;

use Flow\Model\PostRevision;
use Flow\SpamFilter\SpamRegex;
use Flow\Tests\PostRevisionTestCase;
use Title;

/**
 * @group Flow
 */
class SpamRegexTest extends PostRevisionTestCase {
	/**
	 * @var SpamRegex
	 */
	protected $spamFilter;

	public function spamProvider() {
		return [
			[
				// default new topic title revision - no spam
				$this->generateObject(),
				null,
				true
			],
			[
				// revision with spam
				$this->generateObject( [ 'rev_content' => 'http://spam', 'rev_flags' => 'html' ] ),
				null,
				false
			],
		];
	}

	/**
	 * @dataProvider spamProvider
	 */
	public function testSpam( PostRevision $newRevision, PostRevision $oldRevision = null, $expected ) {
		$title = Title::newFromText( 'UTPage' );

		$status = $this->spamFilter->validate( $this->getMock( 'IContextSource' ), $newRevision, $oldRevision, $title, $title );
		$this->assertEquals( $expected, $status->isOK() );
	}

	protected function setUp() {
		parent::setUp();

		// create a dummy filter
		$this->setMwGlobals( 'wgSpamRegex', [ '/http:\/\/spam/' ] );

		// create spam filter
		$this->spamFilter = new SpamRegex;
		if ( !$this->spamFilter->enabled() ) {
			$this->markTestSkipped( 'SpamRegex not enabled' );
		}
	}
}
