<?php

namespace Flow\Tests\Parsoid\Fixer;

use Flow\Model\UUID;
use Flow\Parsoid\ContentFixer;
use Flow\Parsoid\Fixer\Redlinker;
use Flow\Parsoid\Utils;
use Flow\Tests\PostRevisionTestCase;
use Html;
use Title;

/**
 * @group Flow
 */
class RedlinkerTest extends PostRevisionTestCase {

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
	public function testAppliesRedLinks( $message, $anchor, $expect ) {
		$fixer = new ContentFixer( new Redlinker( $this->getMock( 'LinkBatch' ) ) );
		$result = $fixer->apply( $anchor, Title::newMainPage() );
		$this->assertContains( $expect, $result, $message );
	}

	public function testRegistersPost() {
		$anchor = Html::element( 'a', array(
			'rel' => 'mw:WikiLink',
			'href' => './Main_Page',
		), 'Main Page' );

		$post = $this->generateObject( array(
			// pretend not to be topic title (they're not parsed, so ignored)
			'tree_parent_id' => UUID::create()->getBinary(),
			// set content with link
			'rev_content' => $anchor,
			'rev_flags' => 'html'
		) );

		$batch = $this->getMock( 'LinkBatch' );
		$batch->expects( $this->once() )
			->method( 'addObj' )
			->with( new MethodReturnsConstraint(
				'getDBkey',
				$this->matches( 'Main_Page' )
			) );

		$fixer = new ContentFixer( new Redlinker( $batch ) );
		$fixer->registerRecursive( $post );
		$fixer->resolveRecursive();
	}

	public function testCollectsLinks() {
		$anchor = Html::element( 'a', array(
			'rel' => 'mw:WikiLink',
			'href' => './Main_Page',
		), 'Main Page' );

		$batch = $this->getMock( 'LinkBatch' );
		$batch->expects( $this->once() )
			->method( 'addObj' )
			->with( new MethodReturnsConstraint(
				'getDBkey',
				$this->matches( 'Main_Page' )
			) );

		$dom = Utils::createDOM( '<?xml encoding="utf-8"?>' . $anchor );
		$redlink =  new Redlinker( $batch );
		$redlink->recursive( $dom->getElementsByTagName( 'a' )->item( 0 ) );
		$redlink->resolve();
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
