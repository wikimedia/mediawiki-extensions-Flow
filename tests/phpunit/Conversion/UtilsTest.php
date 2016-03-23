<?php

namespace Flow\Tests\Conversion;

use Flow\Exception\WikitextException;
use Flow\Conversion\Utils;
use Flow\Tests\FlowTestCase;
use Title;

/**
 * @group Flow
 */
class ConversionUtilsTest extends FlowTestCase {

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

	/**
	 * Test topic-title-plaintext
	 *
	 * @dataProvider topicTitleProvider
	 */
	public function testTopicTitle( $message, $wikitext, $expectedHtml, $expectedPlaintext ) {
		$this->setMwGlobals( 'wgScript', '/w/index.php' );

		$html = Utils::convert( 'topic-title-wikitext', 'topic-title-html', $wikitext, Title::newMainPage() );
		$this->assertEquals( $expectedHtml, $html, "$message: html" );

		$plaintext = Utils::convert( 'topic-title-wikitext', 'topic-title-plaintext', $wikitext, Title::newMainPage() );
		$this->assertEquals( $expectedPlaintext, $plaintext, "$message: plaintext" );
	}

	static public function topicTitleProvider() {
		return array(
			array(
				'External links not processed',
				'[http://example.com Example]',
				'[http://example.com Example]',
				'[http://example.com Example]',
			),
			array(
				'Bold and italics not processed',
				"'''Bold''' and ''italics''",
				"&#039;&#039;&#039;Bold&#039;&#039;&#039; and &#039;&#039;italics&#039;&#039;",
				"'''Bold''' and ''italics''",
			),
			array(
				'Script tags are treated as text',
				'<script>alert(\'Test\');</script>',
				'&lt;script&gt;alert(&#039;Test&#039;);&lt;/script&gt;',
				'<script>alert(\'Test\');</script>',
			),
			array(
				'Entities processed',
				'&amp;&#x27;',
				'&amp;&#039;',
				'&\'',
			),
			array(
				'Internal links are converted to plaintext',
				'[[asdfasdferqwer389]] is a place',
				'<a href="/w/index.php?title=Asdfasdferqwer389&amp;action=edit&amp;redlink=1" class="new" title="Asdfasdferqwer389 (page does not exist)">asdfasdferqwer389</a> is a place',
				'asdfasdferqwer389 is a place',
			),
			array(
				'Quotes are preserved',
				'\'Single quotes\' "Double quotes"',
				'&#039;Single quotes&#039; &quot;Double quotes&quot;',
				'\'Single quotes\' "Double quotes"',
			),
		);
	}
}
