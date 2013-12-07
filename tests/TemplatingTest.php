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
				// title text from parsoid
				'Talk:Flow/Bugs',
				// expect string
				// @fixme easily breakable, depends on url order
				htmlentities( 'Talk:Flow/Bugs&action=edit&redlink=1' ),
			),

			array(
				'Subpage redlink application',
				// title text from parsoid
				'/SubPage',
				// expect string
				htmlentities( 'Main_Page/SubPage&action=edit&redlink=1' ),
			),

			array(
				'Link containing html entities should be properly handled',
				// title text from parsoid
				'Foo&bar',
				// expect string
				htmlspecialchars( 'Foo%26bar&action=edit&redlink=1' ),
			),
		);
	}

	/**
	 * @dataProvider redLinkProvider
	 */
	public function testRedLinks( $message, $saHref, $expect ) {
		// needs a page to resolve subpage links against
		$this->setMwGlobals( 'wgTitle', Title::newMainPage() );

		$parsoid = htmlentities( json_encode( array( 'sa' => array( 'href' => $saHref ) ) ) );
		$uid = Model\UUID::create( '0509b4bf4b2d616abe79080027a08222' );
		$rev = Model\PostRevision::fromStorageRow( array(
			'rev_id' => $uid->getBinary(),
			'tree_rev_id' => $uid->getBinary(),
			'rev_content' => '<a rel="mw:WikiLink" data-parsoid="' . $parsoid . '">' . htmlspecialchars( $saHref ) . '</a>',
			'rev_flags' => 'html'
		) + self::$postRow );

		$content = $this->mockTemplating()->getContent( $rev, 'html' );
		$this->assertContains( $expect, $content, $message );
	}

	protected function mockTemplating() {
		$usernames = $this->getMockBuilder( 'Flow\Data\UserNameBatch' )
			->disableOriginalConstructor()
			->getMock();
		$urlGenerator = $this->getMockBuilder( 'Flow\UrlGenerator' )
			->disableOriginalConstructor()
			->getMock();
		$output = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		return new Templating( $usernames, $urlGenerator, $output );
	}
}
