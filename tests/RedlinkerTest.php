<?php

namespace Flow;

use Title;

class RedlinkerTest extends \MediaWikiTestCase {

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
	public function testApplysRedLinks( $message, $saHref, $expect ) {
		$anchor = \Html::element( 'a', array(
			'rel' => 'mw:WikiLink',
			'data-parsoid' => json_encode( array( 'sa' => array( 'href' => $saHref ) ) ),
		), $saHref );
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
