<?php

// phpcs:disable Generic.Files.LineLength -- Long html test examples

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\Fixer\EmptyNodeFixer;
use Flow\Parsoid\ContentFixer;
use Title;

/**
 * @covers \Flow\Parsoid\Fixer\EmptyNodeFixer
 *
 * @group Flow
 */
class EmptyNodeFixerTest extends \MediaWikiTestCase {

	public static function emptyNodeProvider() {
		return [
			[
				'Rewrites href of link surrounding image',
				'<figure class="mw-default-size" typeof="mw:Image/Thumb" data-parsoid="...">'
					. '<a href="http://mywiki/wiki/./File:Example.jpg" data-parsoid="...">'
					. '<img resource="./File:Example.jpg" src="//upload.wikimedia.org/wikipedia/mediawiki/thumb/a/a9/Example.jpg/220px-Example.jpg" data-parsoid="..." height="147" width="220"/>'
					. '</a>'
					. '<figcaption data-parsoid="..."> caption</figcaption>'
					. '</figure>',
				'<figure class="mw-default-size" typeof="mw:Image/Thumb" data-parsoid="...">'
					. '<a href="./File:Example.jpg" data-parsoid="...">'
					. '<img resource="./File:Example.jpg" src="//upload.wikimedia.org/wikipedia/mediawiki/thumb/a/a9/Example.jpg/220px-Example.jpg" data-parsoid="..." height="147" width="220">'
					. '</a>'
					. '<figcaption data-parsoid="..."> caption</figcaption>'
					. '</figure>',
			],
		];
	}

	/**
	 * @rdataProvider emptyNodeProvider
	 */
/*	public function testEmptyNodeFixer( $message, $expectedAfter, $before ) {
		$fixer = new ContentFixer( new EmptyNodeFixer );
		$result = $fixer->apply( $before, Title::newMainPage() );
		$this->assertEquals( $expectedAfter, $result, $message );
	}*/

	public function testEmptyNodeFixer() {
		$html = '<body><p><a id="notempty">Hello</a><a id="empty"></a><a id="image"><img src="foo"></a></p></body>';
		$dom = ContentFixer::createDOM( $html );
		$notemptyLink = $dom->getElementById( 'notempty' );
		$emptyLink = $dom->getElementById( 'empty' );
		$imageLink = $dom->getElementById( 'image' );
		$imageNode = $dom->getElementsByTagName( 'img' )->item( 0 );

		$this->assertEquals( $notemptyLink->childNodes->length, 1, 'non-empty link has one child before fixer' );
		$this->assertEquals( $emptyLink->childNodes->length, 0, 'empty link has no children before fixer' );
		$this->assertEquals( $imageLink->childNodes->length, 1, 'image link has one child before fixer' );
		$this->assertEquals( $imageNode->childNodes->length, 0, 'img has no children before fixer' );

		$fixer = new ContentFixer( new EmptyNodeFixer );
		$fixer->applyToDom( $dom, Title::newMainPage() );

		$this->assertEquals( $notemptyLink->childNodes->length, 1, 'non-empty link has one child after fixer' );
		$this->assertEquals( $emptyLink->childNodes->length, 1, 'empty link has one child after fixer' );
		$this->assertEquals( $emptyLink->childNodes->item( 0 )->nodeType, XML_TEXT_NODE, 'empty link child is a text node' );
		$this->assertEquals( $emptyLink->childNodes->item( 0 )->data, '', 'empty link child text node is empty' );
		$this->assertEquals( $imageLink->childNodes->length, 1, 'image link has one child after fixer' );
		$this->assertEquals( $imageNode->childNodes->length, 0, 'img has no children after fixer' );
	}
}
