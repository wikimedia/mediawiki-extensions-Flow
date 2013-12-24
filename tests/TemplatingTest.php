<?php

namespace Flow;

use Title;

class TemplatingTest extends \MediaWikiTestCase {

	// Default values for PostRevision::newFromRow to work
	static protected $postRow = array(
		'rev_id' => null,
		'rev_user_id' => null,
		'rev_user_text' => null,
		'rev_parent_id' => null,
		'rev_change_type' => null,
		'rev_flags' => null,
		'rev_content' => null,
		'rev_mod_state' => null,
		'rev_mod_user_id' => null,
		'rev_mod_user_text' => null,
		'rev_mod_timestamp' => null,
		'tree_parent_id' => null,
		'tree_rev_id' => null,
		'tree_rev_descendant_id' => null,
		'tree_orig_create_time' => null,
		'tree_orig_user_id' => null,
		'tree_orig_user_text' => null,
	);

	static public function redLinkProvider() {
		return array(
			array(
				'Basic redlink application',
				// html from parsoid for: [[Talk:Flow/Bugs]]
				'<a rel="mw:WikiLink" href="./Talk:Flow/Bugs" data-parsoid=\'{"stx":"simple","a":{"href":"./Talk:Flow/Bugs"},"sa":{"href":"Talk:Flow/Bugs"},"dsr":[0,18,2,2]}\'>Talk:Flow/Bugs</a>',
				// expect string
				// @fixme easily breakable, depends on url order
				htmlentities( 'Talk:Flow/Bugs&action=edit&redlink=1' ),
			),

			array(
				'Subpage redlink application',
				// html from parsoid for: [[/SubPage]]
				'<a rel="mw:WikiLink" href=".//SubPage" data-parsoid=\'{"stx":"simple","a":{"href":".//SubPage"},"sa":{"href":"/SubPage"},"dsr":[0,12,2,2]}\'>/SubPage</a>',
				// expect string
				htmlentities( 'Main_Page/SubPage&action=edit&redlink=1' ),
			),

			array(
				'Link containing html entities should be properly handled',
				// html from parsoid for: [[Foo&Bar]]
				'<a rel="mw:WikiLink" href="./Foo&amp;Bar" data-parsoid=\'{"stx":"simple","a":{"href":"./Foo&amp;Bar"},"sa":{"href":"Foo&amp;Bar"},"dsr":[0,11,2,2]}\'>Foo&amp;Bar</a>',
				// expect string
				'Foo&amp;Bar',
			),

			array(
				'Link containing UTF-8 content passes through as UTF-8',
				// html from parsoid for: [[Foo|test – test]]
				'<a rel="mw:WikiLink" href="./Foo" data-parsoid=\'{"stx":"piped","a":{"href":"./Foo"},"sa":{"href":"Foo"},"dsr":[0,19,6,2]}\'>test – test</a>',
				// title text from parsoid
				// expect string
				'test – test',
			),
		);
	}

	/**
	 * @dataProvider redLinkProvider
	 */
	public function testRedLinks( $message, $content, $expect ) {

		// needs a page to resolve subpage links against
		$this->setMwGlobals( 'wgTitle', Title::newMainPage() );

		$uid = Model\UUID::create( '0509b4bf4b2d616abe79080027a08222' );
		$rev = Model\PostRevision::fromStorageRow( array(
			'rev_id' => $uid->getBinary(),
			'tree_rev_id' => $uid->getBinary(),
			'rev_content' => $content,
			'rev_flags' => 'html'
		) + self::$postRow );

		$result = $this->mockTemplating()->getContent( $rev, 'html' );
		$this->assertContains( $expect, $result, $message );
	}

	protected function mockTemplating() {
		$urlGenerator = $this->getMockBuilder( 'Flow\UrlGenerator' )
			->disableOriginalConstructor()
			->getMock();
		$output = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		return new Templating( $urlGenerator, $output );
	}
}
