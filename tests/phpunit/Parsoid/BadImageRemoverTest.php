<?php

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\BadImageRemover;
use Flow\Parsoid\Utils;
use Title;

class BadImageRemoverTest extends \MediaWikiTestCase {

	/**
	 * Note that this must return html rather than roundtripping wikitext
	 * through parsoid because that is not current available from the jenkins
	 * test runner/
	 */
	public static function imageRemovalProvider() {
		return array(
			array(
				'Passes through allowed good images',
				// expected html after filtering
				'<p><span class="mw-default-size" typeof="mw:Image"><a href="./File:Image.jpg"><img resource="./File:Image.jpg" src="//upload.wikimedia.org/wikipedia/commons/7/78/Image.jpg" height="500" width="500"></a></span> and other stuff</p>',
				// input html
				'<p><span class="mw-default-size" typeof="mw:Image"><a href="./File:Image.jpg"><img resource="./File:Image.jpg" src="//upload.wikimedia.org/wikipedia/commons/7/78/Image.jpg" height="500" width="500"></a></span> and other stuff</p>',
				// accept/decline callback
				function() { return false; }
			),

			array(
				'Keeps unknown images',
				// expected html after filtering
				'<meta typeof="mw:Placeholder" data-parsoid="...">',
				// input html
				'<meta typeof="mw:Placeholder" data-parsoid="...">',
				// accept/decline callback
				function() { return true; }
			),

			array(
				'Strips declined images',
				// expected html after filtering
				'<p> and other stuff</p>',
				// input html
				'<p><span class="mw-default-size" typeof="mw:Image"><a href="./File:Image.jpg"><img resource="./File:Image.jpg" src="//upload.wikimedia.org/wikipedia/commons/7/78/Image.jpg" height="500" width="500"></a></span> and other stuff</p>',
				// accept/decline callback
				function() { return true; }
			),
		);
	}
	/**
	 * @dataProvider imageRemovalProvider
	 */
	public function testImageRemoval( $message, $expect, $content, $badImageFilter ) {
		$fixer = new BadImageRemover( $badImageFilter );
		$dom = Utils::createDOM( '<?xml encoding="utf-8"?><body>' . $content . '</body>' );
		$fixer->apply( $dom, Title::newMainPage() );
		$result = Utils::getInnerHtml( $dom->getElementsByTagName( 'body' )->item( 0 ) );
		$this->assertEquals( $expect, trim( $result ), $message );
	}
}
