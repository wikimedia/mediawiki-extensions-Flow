<?php

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\Utils;
use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class ParsoidUtilsTest extends FlowTestCase {

	static public function createDomProvider() {
		return array(
			array(
				'A document with multiple matching ids is valid parser output',
				'<body><a id="foo">foo</a><a id="foo">bar</a></body>'
			),
			array(
				'HTML5 tags, such as figcaption, are valid html',
				'<body><figcaption /></body>'
			),
		);
	}

	/**
	 * @dataProvider createDomProvider
	 */
	public function testCreateDomErrorModes( $message, $content ) {
		$this->assertInstanceOf( 'DOMDocument', Utils::createDOM( $content ), $message );
	}
}
