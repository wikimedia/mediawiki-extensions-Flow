<?php

// phpcs:disable Generic.Files.LineLength -- Long html test examples

namespace Flow\Tests\Conversion;

use DOMDocument;
use Flow\Conversion\Utils;
use Flow\Exception\WikitextException;
use Flow\Tests\FlowTestCase;
use Title;

/**
 * @covers \Flow\Conversion\Utils
 *
 * @group Flow
 */
class ConversionUtilsTest extends FlowTestCase {

	public static function createDomProvider() {
		return [
			[
				'A document with multiple matching ids is valid parser output',
				'<body><a id="foo">foo</a><a id="foo">bar</a></body>'
			],
			[
				'HTML5 tags, such as figcaption, are valid html',
				'<body><figcaption /></body>'
			],
		];
	}

	/**
	 * @dataProvider createDomProvider
	 */
	public function testCreateDomErrorModes( $message, $content ) {
		$this->assertInstanceOf( DOMDocument::class, Utils::createDOM( $content ), $message );
	}

	public static function createRelativeTitleProvider() {
		return [
			[
				'message' => 'strips leading ./ and treats as non-relative',
				'expectedTitleText' => 'Foo.jpg',
				'inputText' => './File:Foo.jpg',
				'inputTitleText' => 'Main_Page',
			],
			[
				'message' => 'two level upwards traversal',
				'expectedTitleText' => 'Bar.jpg',
				'inputText' => '../../File:Bar.jpg',
				'inputTitleText' => 'Main_Page/And/Subpage',
			],
			[
				'message' => 'appends non-relative text to the title',
				'expectedTitleText' => 'Main_Page/Image.jpg',
				'inputText' => '/Image.jpg',
				'inputTitleText' => 'File:Main_Page',
			],
		];
	}

	/**
	 * @dataProvider createRelativeTitleProvider
	 */
	public function testResolveSubpageTraversal( $message, $expectedTitleText, $inputText, $inputTitleText ) {
		$expectTitle = Title::makeTitle( NS_FILE, $expectedTitleText );
		$title = Title::newFromText( $inputTitleText );

		$result = Utils::createRelativeTitle( $inputText, $title );

		if ( $expectTitle === null ) {
			$this->assertNull( $expectTitle, $message );
		} elseif ( $expectTitle instanceof Title ) {
			$this->assertInstanceOf( Title::class, $result, $message );
			$this->assertEquals( $expectTitle->getPrefixedText(), $result->getPrefixedText(), $message );
		} else {
			$this->assertEquals( $expectTitle, $result, $message );
		}
	}

	public static function wikitextRoundtripProvider() {
		return [
			[
				'italic text',
				// text & expect
				"''italic text''",
				// title
				Title::newMainPage(),
			],
			[
				'bold text',
				// text & expect
				"'''bold text'''",
				// title
				Title::newMainPage(),
			],
		];
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
		$this->markTestSkipped( 'If Parsoid is enabled an actual network request is run and that is not allowed. See T262443' );
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

	public static function topicTitleProvider() {
		return [
			[
				'External links not processed',
				'[http://example.com Example]',
				'[http://example.com Example]',
				'[http://example.com Example]',
			],
			[
				'Bold and italics not processed',
				"'''Bold''' and ''italics''",
				"&#039;&#039;&#039;Bold&#039;&#039;&#039; and &#039;&#039;italics&#039;&#039;",
				"'''Bold''' and ''italics''",
			],
			[
				'Script tags are treated as text',
				'<script>alert(\'Test\');</script>',
				'&lt;script&gt;alert(&#039;Test&#039;);&lt;/script&gt;',
				'<script>alert(\'Test\');</script>',
			],
			[
				'Entities processed',
				'&amp;&#x27;',
				'&amp;&#039;',
				'&\'',
			],
			[
				'Internal links are converted to plaintext',
				'[[asdfasdferqwer389]] is a place',
				'<a href="/w/index.php?title=Asdfasdferqwer389&amp;action=edit&amp;redlink=1" class="new" title="Asdfasdferqwer389 (page does not exist)">asdfasdferqwer389</a> is a place',
				'asdfasdferqwer389 is a place',
			],
			[
				'Quotes are preserved',
				'\'Single quotes\' "Double quotes"',
				'&#039;Single quotes&#039; &quot;Double quotes&quot;',
				'\'Single quotes\' "Double quotes"',
			],
		];
	}

	/**
	 * @dataProvider provideEncodeHeadInfo
	 */
	public function testEncodeHeadInfo( $message, $input, $expectedOutput ) {
		$this->assertEquals( $expectedOutput, Utils::encodeHeadInfo( $input ), $message );
	}

	public static function provideEncodeHeadInfo() {
		$parsoidVersion = Utils::PARSOID_VERSION;
		return [
			[
				'Head with base tag',
				'<html><head><base href="foo"></head><body><p>Hello</p></body></html>',
				'<body parsoid-version="' . $parsoidVersion . '" base-url="foo"><p>Hello</p></body>'
			],
			[
				'Head with base tag with no href',
				'<html><head><base></head><body><p>Hello</p></body></html>',
				'<body parsoid-version="' . $parsoidVersion . '"><p>Hello</p></body>'
			],
			[
				'Head with base tag with no href',
				'<html><head><base></head><body><p>Hello</p></body></html>',
				'<body parsoid-version="' . $parsoidVersion . '"><p>Hello</p></body>'
			],
			[
				'Parsoid example',
				'<!DOCTYPE html><html prefix="dc: http://purl.org/dc/terms/ mw: https://mediawiki.org/rdf/"><head prefix="mwr: http://en.wikipedia.org/wiki/Special:Redirect/"><meta charset="utf-8"/><meta property="mw:pageNamespace" content="0"/><meta property="isMainPage" content="true"/><meta property="mw:html:version" content="2.1.0"/><link rel="dc:isVersionOf" href="//en.wikipedia.org/wiki/Main_Page"/><title></title><base href="//en.wikipedia.org/wiki/"/><link rel="stylesheet" href="//en.wikipedia.org/w/load.php?modules=mediawiki.legacy.commonPrint%2Cshared%7Cmediawiki.skinning.content.parsoid%7Cmediawiki.skinning.interface%7Cskins.vector.styles%7Csite.styles%7Cext.cite.style%7Cext.cite.styles%7Cmediawiki.page.gallery.styles&amp;only=styles&amp;skin=vector"/><meta http-equiv="content-language" content="en"/><meta http-equiv="vary" content="Accept"/></head><body id="mwAA" lang="en" class="mw-content-ltr sitedir-ltr ltr mw-body-content parsoid-body mediawiki mw-parser-output" dir="ltr"><section data-mw-section-id="0" id="mwAQ"><p id="mwAg">Hello <a rel="mw:WikiLink" href="./World" title="World" id="mwAw">world</a></p></section></body></html>',
				'<body id="mwAA" lang="en" dir="ltr" parsoid-version="' . $parsoidVersion . '" base-url="//en.wikipedia.org/wiki/"><section data-mw-section-id="0" id="mwAQ"><p id="mwAg">Hello <a rel="mw:WikiLink" href="./World" title="World" id="mwAw">world</a></p></section></body>'
			],
		];
	}

	/**
	 * @dataProvider provideDecodeHeadInfo
	 */
	public function testDecodeHeadInfo( $message, $input, $expectedOutput ) {
		$actual = Utils::decodeHeadInfo( $input );
		$actual = str_replace( '/>', '>', $actual );
		$this->assertSame( $expectedOutput, $actual, $message );
	}

	public static function provideDecodeHeadInfo() {
		return [
			[
				'Body tag with base-url',
				'<body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><p>Hello</p></body>',
				'<html><head><base href="//en.wikipedia.org/wiki/"></head><body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><p>Hello</p></body></html>'
			],
			[
				'Body tag without base-url',
				'<body><p>Hello</p></body>',
				'<html><head></head><body><p>Hello</p></body></html>'
			],
			[
				'Unwrapped body tag',
				'<p>Hello</p>',
				'<html><head></head><body><p>Hello</p></body></html>'
			],
			[
				'Plain text',
				'Hello',
				'<html><head></head><body>Hello</body></html>'
			],
			[
				'Body tag with style tag',
				'<body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style>.mw-parser-output { background-color: gray; }</style><p>Hello</p></body>',
				'<html><head><base href="//en.wikipedia.org/wiki/"></head><body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style>.mw-parser-output { background-color: gray; }</style><p>Hello</p></body></html>'
			],
			[
				'Body tag with style tag and attributes',
				'<body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style typeof="mw:Extension/templatestyles mw:Transclusion">.mw-parser-output { background-color: gray; }</style><p>Hello</p></body>',
				'<html><head><base href="//en.wikipedia.org/wiki/"></head><body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style typeof="mw:Extension/templatestyles mw:Transclusion">.mw-parser-output { background-color: gray; }</style><p>Hello</p></body></html>'
			],
			[
				'Body tag with multiple style tags',
				'<body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style>.mw-parser-output { background-color: gray; }</style><p>Hello</p><style>.mw-parser-output { background-color: gray; }</style></body>',
				'<html><head><base href="//en.wikipedia.org/wiki/"></head><body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style>.mw-parser-output { background-color: gray; }</style><p>Hello</p><style>.mw-parser-output { background-color: gray; }</style></body></html>'
			],
			[
				'Body tag with style tag',
				'<body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style test=">">.mw-parser-output { background-color: gray; }</style><p>Hello</p></body>',
				'<html><head><base href="//en.wikipedia.org/wiki/"></head><body base-url="//en.wikipedia.org/wiki/" parsoid-version="0.1.2"><style test="&gt;">.mw-parser-output { background-color: gray; }</style><p>Hello</p></body></html>'
			],
		];
	}

	public static function provideTransformationFormats() {
		return [
			[
				'from' => 'topic-title-wikitext',
				'to' => 'topic-title-plaintext',
				'content' => 'Foobar',
				'expected' => 'Foobar'
			],
			[
				'from' => 'wikitext',
				'to' => 'wikitext',
				'content' => '== Foobar ==',
				'expected' => '== Foobar =='
			],
			[
				'from' => 'wikitext',
				'to' => 'html',
				'content' => '',
				'expected' => ''
			],
			[
				'from' => 'html',
				'to' => 'html',
				'content' => '<h2>Foobar</h2>',
				'expected' => '<h2>Foobar</h2>'
			],
		];
	}

	/**
	 * @dataProvider provideTransformationFormats
	 */
	public function testConvert( $from, $to, $content, $expected ) {
		$title = $this->createNoOpMock( Title::class );
		$actual = Utils::convert( $from, $to, $content, $title );
		$this->assertSame( $expected, $actual );
	}

	public function testGetInnerHtml() {
		$dom = new DOMDocument();
		$dom->loadHTML( '<div><p>Test content "Foobar" </p></div>' );

		$divNode = $dom->getElementsByTagName( 'div' )->item( 0 );

		$innerHtml = Utils::getInnerHtml( $divNode );

		$expectedInnerHtml = '<p>Test content "Foobar" </p>';
		$this->assertEquals( $expectedInnerHtml, $innerHtml );
	}

	public function testGetParsoidVersion() {
		$html = '<body parsoid-version="1.2.3">Test content "Foobar" </body>';

		$version = Utils::getParsoidVersion( $html );
		$expectedVersion = '1.2.3';
		$this->assertEquals( $expectedVersion, $version );
	}
}
