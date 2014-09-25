<?php

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\Utils;
use Flow\Tests\FlowTestCase;
use Title;

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

	static public function createRelativeTitleProvider() {
		return array(
			array(
				'some test',
				// expect
				Title::newFromText( 'File:Foo.jpg' ),
				// input text
				'./File:Foo.jpg',
				// relative to title
				Title::newMainPage()
			),

			// The following tests are all basically a hack the works because
			// we "know" that parsoid always uses enough ../../ or ./ to
			// get back to root.

			array(
				'strips leading ./ and treats as non-relative',
				// expect
				Title::newFromText( 'File:Foo.jpg' ),
				// input text
				'./File:Foo.jpg',
				// relative tot title
				Title::newFromText( 'Main_Page/Foo' ),
			),

			array(
				'two level upwards traversal',
				// expect
				Title::newFromText( 'File:Bar.jpg' ),
				// input text
				'../../File:Bar.jpg',
				// relative to title
				Title::newFromText( 'Main_Page/And/Subpage' ),
			),
		);
	}

	/**
	 * @dataProvider createRelativeTitleProvider
	 */
	public function testResolveSubpageTraversal( $message, $expect, $text, Title $title ) {
		$result = Utils::createRelativeTitle( $text, $title );

		if ( $expect === null ) {
			$this->assertNull( $expect, $message );
		} elseif ( $expect instanceof Title ) {
			$this->assertInstanceOf( 'Title', $result, $message );
			$this->assertEquals( $expect->getPrefixedText(), $result->getPrefixedText(), $message );
		} else {
			$this->assertEquals( $expect, $result, $message );
		}
	}
}
