<?php

namespace Flow\Tests\Parsoid;

use Flow\Exception\WikitextException;
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
				'strips leading ./ and treats as non-relative',
				// expect
				Title::newFromText( 'File:Foo.jpg' ),
				// input text
				'./File:Foo.jpg',
				// relative to title
				Title::newMainPage()
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

	static public function wikitextRoundtripProvider() {
		return array(
			array(
				'italic text',
				// text & expect
				"''italic text''",
				// title
				Title::newMainPage(),
			),
			array(
				'bold text',
				// text & expect
				"'''bold text'''",
				// title
				Title::newMainPage(),
			),
		);
	}

	/**
	 * Test full roundtrip (wikitext -> html -> wikitext)
	 *
	 * It doesn't make sense to test only a specific path, since Parsoid's HTML
	 * may change beyond our control & it doesn't really matter to us what
	 * exactly the HTML looks like, as long as Parsoid is able to understand it.
	 *
	 * @dataProvider wikitextRoundtripProvider
	 */
	public function testwikitextRoundtrip( $message, $expect, Title $title ) {
		// Check for Parsoid
		try {
			$html = Utils::convert( 'wikitext', 'html', $expect, $title );
			$wikitext = Utils::convert( 'html', 'wikitext', $html, $title );
			$this->assertEquals( $expect, trim( $wikitext ), $message );
		} catch ( WikitextException $excep ) {
			$this->markTestSkipped( 'Parsoid not enabled' );
		}
	}
}
