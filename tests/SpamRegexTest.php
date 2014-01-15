<?php

namespace Flow\Tests;

use Flow\SpamFilter\SpamRegex;
use Flow\Model\PostRevision;
use Title;

class SpamRegexTest extends PostRevisionTestCase {
	/**
	 * @var SpamRegex
	 */
	protected $spamFilter;

	public function spamProvider() {
		return array(
			array(
				// default new topic title revision - no spam
				$this->generateObject(),
				null,
				true
			),
			array(
				// revision with spam
				$this->generateObject( array( 'rev_content' => 'http://spam', 'rev_flags' => 'html' ) ),
				null,
				false
			),
		);
	}

	/**
	 * @dataProvider spamProvider
	 */
	public function testSpam( PostRevision $newRevision, PostRevision $oldRevision = null, $expected ) {
		if ( !$this->spamFilter->enabled() ) {
			$this->markTestSkipped( 'SpamRegex not enabled' );
		}

		$title = Title::newFromText( 'UTPage' );

		$status = $this->spamFilter->validate( $newRevision, $oldRevision, $title );
		$this->assertEquals( $status->isOK(), $expected );
	}

	protected function setUp() {
		parent::setUp();

		// create spam filter
		$this->spamFilter = new SpamRegex;

		// create a dummy filter
		global $wgSpamRegex;
		$wgSpamRegex = array( '/http:\/\/spam/' );
	}
}
