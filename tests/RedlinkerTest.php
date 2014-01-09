<?php

namespace Flow;

use Title;

class RedlinkerTest extends \MediaWikiTestCase {

	// Default values for PostRevision::newFromRow to work
	static protected $postRow = array(
		'rev_id' => null,
		'rev_user_id' => null,
		'rev_user_ip' => null,
		'rev_parent_id' => null,
		'rev_change_type' => null,
		'rev_flags' => null,
		'rev_content' => null,
		'rev_mod_state' => null,
		'rev_mod_user_id' => null,
		'rev_mod_user_ip' => null,
		'rev_mod_timestamp' => null,
		'tree_parent_id' => null,
		'tree_rev_id' => null,
		'tree_rev_descendant_id' => null,
		'tree_orig_create_time' => null,
		'tree_orig_user_id' => null,
		'tree_orig_user_ip' => null,
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
				'>Foo&amp;Bar</a>',
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
	public function testApplysRedLinks( $message, $anchor, $expect ) {
		$redlink = new Redlinker( Title::newMainPage(), $this->getMock( 'LinkBatch' ) );
		$result = $redlink->apply( $anchor );
		$this->assertContains( $expect, $result, $message );
	}

	public function testRegistersPost() {
		$saHref = 'Main_Page';
		$anchor = \Html::element( 'a', array(
			'rel' => 'mw:WikiLink',
			'data-parsoid' => json_encode( array( 'sa' => array( 'href' => $saHref ) ) ),
		), $saHref );

		// We don't need a real id, just something reasonable.
		$uid = Model\UUID::getComparisonUUID( null );
		$post = Model\PostRevision::fromStorageRow( array(
			'rev_id' => $uid,
			'tree_rev_id' => $uid,
			'tree_rev_descendant_id' => $uid,
			'tree_parent_id' => $uid,
			'rev_content' => $anchor,
			'rev_flags' => 'html',
		) + self::$postRow );
		$post->setChildren( array() );

		$batch = $this->getMock( 'LinkBatch' );
		$batch->expects( $this->once() )
			->method( 'addObj' )
			->with( new MethodReturnsConstraint(
				'getDBkey',
				$this->matches( $saHref )
			) );

		$redlinker = new Redlinker( Title::newMainPage(), $batch );
		$redlinker->registerPost( $post );
		$redlinker->resolveLinkStatus();
	}

	public function testCollectsLinks() {
		$saHref = 'Main_Page';
		$anchor = \Html::element( 'a', array(
			'rel' => 'mw:WikiLink',
			'data-parsoid' => json_encode( array( 'sa' => array( 'href' => $saHref ) ) ),
		), $saHref );

		$batch = $this->getMock( 'LinkBatch' );
		$batch->expects( $this->once() )
			->method( 'addObj' )
			->with( new MethodReturnsConstraint(
				'getDBkey',
				$this->matches( $saHref )
			) );

		$redlinker = new Redlinker( Title::newMainPage(), $batch );
		$redlinker->collectLinks( $anchor );
	}
}

class MethodReturnsConstraint extends \PHPUnit_Framework_Constraint {
	public function __construct( $method, \PHPUnit_Framework_Constraint $constraint ) {
		$this->method = $method;
		$this->constraint = $constraint;
	}

	protected function matches( $other ) {
		return $this->constraint->matches( call_user_func( array( $other, $this->method ) ) );
	}

	public function toString() {
		return $this->constraint->toString();
	}

	protected function failureDescription( $other ) {
		return $this->constraint->failureDescription( $other ) . " from {$this->method} method";
	}
}
