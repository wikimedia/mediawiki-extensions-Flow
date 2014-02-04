<?php

namespace Flow\Tests;

use Flow\ParsoidUtils;

/**
 * @group Flow
 */
class ParsoidUtilsTest extends \MediaWikiTestCase {

	static public function createDomProvider() {
		return array(
			array( '<body><a id="foo">foo</a><a id="foo">bar</a></body>' ),
			array( '<body><figcaption /></body>' ),
		);
	}

	/**
	 * @dataProvider createDomProvider
	 */
	public function testCreateDomErrorModes( $content ) {
		$this->assertInstanceOf( 'DOMDocument', ParsoidUtils::createDOM( $content ) );
	}
}
