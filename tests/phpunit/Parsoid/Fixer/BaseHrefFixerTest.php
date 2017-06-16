<?php

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\Fixer\BaseHrefFixer;
use Flow\Parsoid\ContentFixer;
use Title;

/**
 * @group Flow
 */
class BaseHrefFixerTest extends \MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();
		$this->setMwGlobals( 'wgServer', 'http://mywiki' );
	}

	public static function baseHrefProvider() {
		return [
			[
				'Rewrites href of link surrounding image',
				'<figure class="mw-default-size" typeof="mw:Image/Thumb" data-parsoid=\'{"optList":[{"ck":"thumbnail","ak":"thumb"},{"ck":"caption","ak":"[[test]] caption"}],"dsr":[0,43,2,2]}\'><a href="http://mywiki/wiki/./File:Example.jpg" data-parsoid=\'{"a":{"href":"./File:Example.jpg"},"sa":{},"dsr":[2,null,null,null]}\'><img resource="./File:Example.jpg" src="//upload.wikimedia.org/wikipedia/mediawiki/thumb/a/a9/Example.jpg/220px-Example.jpg" data-parsoid=\'{"a":{"resource":"./File:Example.jpg","height":"147","width":"220"},"sa":{"resource":"File:example.jpg"}}\' height="147" width="220"></a><figcaption data-parsoid=\'{"dsr":[null,41,null,null]}\'> caption</figcaption></figure>',
				'<figure class="mw-default-size" typeof="mw:Image/Thumb" data-parsoid=\'{"optList":[{"ck":"thumbnail","ak":"thumb"},{"ck":"caption","ak":"[[test]] caption"}],"dsr":[0,43,2,2]}\'><a href="./File:Example.jpg" data-parsoid=\'{"a":{"href":"./File:Example.jpg"},"sa":{},"dsr":[2,null,null,null]}\'><img resource="./File:Example.jpg" src="//upload.wikimedia.org/wikipedia/mediawiki/thumb/a/a9/Example.jpg/220px-Example.jpg" data-parsoid=\'{"a":{"resource":"./File:Example.jpg","height":"147","width":"220"},"sa":{"resource":"File:example.jpg"}}\' height="147" width="220"></a><figcaption data-parsoid=\'{"dsr":[null,41,null,null]}\'> caption</figcaption></figure>',
			],
		];
	}

	/**
	 * @dataProvider baseHrefProvider
	 */
	public function testBaseHrefFixer( $message, $expectedAfter, $before ) {
		$fixer = new ContentFixer( new BaseHrefFixer( '/wiki/$1' ) );
		$result = $fixer->apply( $before, Title::newMainPage() );
		$this->assertEquals( $expectedAfter, $result, $message );
	}
}
