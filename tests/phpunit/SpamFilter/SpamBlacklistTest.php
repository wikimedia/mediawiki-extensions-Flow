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
	 * Spam blacklist & whitelist regexes. Examples taken from:
	 *
	 * @see http://meta.wikimedia.org/wiki/Spam_blacklist
	 * @see http://en.wikipedia.org/wiki/MediaWiki:Spam-blacklist
	 * @see http://en.wikipedia.org/wiki/MediaWiki:Spam-whitelist
	 *
	 * @var array
	 */
	protected
		$blacklist = array( '\b01bags\.com\b', 'sytes\.net' ),
		$whitelist = array( 'a5b\.sytes\.net' );

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
				$this->generateObject( array( 'rev_content' => 'http://01bags.com', 'rev_flags' => 'html' ) ),
				null,
				false
			),
			array(
				// revision with domain blacklisted as spam, but subdomain whitelisted
				$this->generateObject( array( 'rev_content' => 'http://a5b.sytes.net', 'rev_flags' => 'html' ) ),
				null,
				true
			),
		);
	}

	/**
	 * @dataProvider spamProvider
	 */
	public function testSpam( PostRevision $newRevision, PostRevision $oldRevision = null, $expected ) {
		$title = Title::newFromText( 'UTPage' );

		$status = $this->spamFilter->validate( $newRevision, $oldRevision, $title );
		$this->assertEquals( $expected, $status->isOK() );
	}

	protected function setUp() {
		parent::setUp();

		// create spam filter
		$this->spamFilter = new SpamBlacklist;
		if ( !$this->spamFilter->enabled() ) {
			$this->markTestSkipped( 'SpamBlacklist not enabled' );
		}

		// alter $wgMemc & write empty shared blacklist, to prevent an attempt
		// to fetch spam blacklist over network
		$this->setMwGlobals( 'wgMemc', new \HashBagOStuff() );
		global $wgMemc, $wgDBname;
		$wgMemc->set( "$wgDBname:spam_blacklist_regexes", array() );

		// local spam lists are read from spam-blacklist & spam-whitelist
		// messages, so change them for this test
		$msgCache = \MessageCache::singleton();
		$msgCache->enable();
		$msgCache->replace( 'Spam-blacklist', implode( "\n", $this->blacklist ) );
		$msgCache->replace( 'Spam-whitelist', implode( "\n", $this->whitelist ) );
		// That only works if the spam blacklist is really reset
		$instance = BaseBlacklist::getInstance( 'spam' );
		$reflProp = new \ReflectionProperty( $instance, 'regexes' );
		$reflProp->setAccessible( true );
		$reflProp->setValue( $instance, false );
	}

	protected function tearDown() {
		// we don't have to restore the original messages, disable() will make
		// sure they're ignored
		$msgCache = \MessageCache::singleton();
		$msgCache->disable();
		parent::tearDown();
	}
}
