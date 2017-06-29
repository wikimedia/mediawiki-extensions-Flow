<?php

namespace Flow\Tests\SpamFilter;

use BaseBlacklist;
use Flow\Model\PostRevision;
use Flow\SpamFilter\SpamBlacklist;
use Flow\Tests\PostRevisionTestCase;
use Title;

/**
 * @group Flow
 */
class SpamBlacklistTest extends PostRevisionTestCase {
	/**
	 * @var SpamBlacklist
	 */
	protected $spamFilter;

	/**
	 * Spam blacklist regexes. Examples taken from:
	 *
	 * @see http://meta.wikimedia.org/wiki/Spam_blacklist
	 * @see http://en.wikipedia.org/wiki/MediaWiki:Spam-blacklist
	 *
	 * @var array
	 */
	protected $blacklist = [ '\b01bags\.com\b', 'sytes\.net' ];

	/**
	 * Spam whitelist regexes. Examples taken from:
	 *
	 * @see http://en.wikipedia.org/wiki/MediaWiki:Spam-whitelist
	 *
	 * @var array
	 */
	protected $whitelist = [ 'a5b\.sytes\.net' ];

	public function spamProvider() {
		return [
			'default new topic title revision - no spam' => [
				$this->generateObject(),
				null,
				true
			],
			'revision with spam' => [
				$this->generateObject( [ 'rev_content' => 'http://01bags.com', 'rev_flags' => 'html' ] ),
				null,
				false
			],
			'revision with domain blacklisted as spam, but subdomain whitelisted' => [
				$this->generateObject( [ 'rev_content' => 'http://a5b.sytes.net', 'rev_flags' => 'html' ] ),
				null,
				true
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

		// create spam filter
		$this->spamFilter = new SpamBlacklist;
		if ( !$this->spamFilter->enabled() ) {
			$this->markTestSkipped( 'SpamBlacklist not enabled' );
		}

		$this->setMwGlobals( 'wgBlacklistSettings', [
			'files' => [],
		] );

		\MessageCache::singleton()->enable();
		$this->insertPage( 'MediaWiki:Spam-blacklist', implode( "\n", $this->blacklist ) );
		$this->insertPage( 'MediaWiki:Spam-whitelist', implode( "\n", $this->whitelist ) );

		// That only works if the spam blacklist is really reset
		$instance = BaseBlacklist::getInstance( 'spam' );
		$reflProp = new \ReflectionProperty( $instance, 'regexes' );
		$reflProp->setAccessible( true );
		$reflProp->setValue( $instance, false );
	}

	protected function tearDown() {
		\MessageCache::singleton()->disable();
		parent::tearDown();
	}
}
