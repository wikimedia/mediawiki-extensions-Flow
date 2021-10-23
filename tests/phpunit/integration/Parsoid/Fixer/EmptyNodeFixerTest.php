<?php

// phpcs:disable Generic.Files.LineLength -- Long html test examples

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\ContentFixer;
use Flow\Parsoid\Fixer\EmptyNodeFixer;
use Title;

/**
 * @covers \Flow\Parsoid\Fixer\EmptyNodeFixer
 *
 * @group Flow
 */
class EmptyNodeFixerTest extends \MediaWikiIntegrationTestCase {

	public function testEmptyNodeFixer() {
		$html = '<body><p><a id="notempty">Hello</a><a id="empty"></a><a id="image"><img src="foo"></a></p></body>';
		$dom = ContentFixer::createDOM( $html );
		$notemptyLink = $dom->getElementById( 'notempty' );
		$emptyLink = $dom->getElementById( 'empty' );
		$imageLink = $dom->getElementById( 'image' );
		$imageNode = $dom->getElementsByTagName( 'img' )->item( 0 );

		$this->assertSame( 1, $notemptyLink->childNodes->length, 'non-empty link has one child before fixer' );
		$this->assertSame( 0, $emptyLink->childNodes->length, 'empty link has no children before fixer' );
		$this->assertSame( 1, $imageLink->childNodes->length, 'image link has one child before fixer' );
		$this->assertSame( 0, $imageNode->childNodes->length, 'img has no children before fixer' );

		$fixer = new ContentFixer( new EmptyNodeFixer );
		$fixer->applyToDom( $dom, Title::newMainPage() );

		$this->assertSame( 1, $notemptyLink->childNodes->length, 'non-empty link has one child after fixer' );
		$this->assertSame( 1, $emptyLink->childNodes->length, 'empty link has one child after fixer' );
		$this->assertEquals( $emptyLink->childNodes->item( 0 )->nodeType, XML_TEXT_NODE, 'empty link child is a text node' );
		$this->assertSame( '', $emptyLink->childNodes->item( 0 )->data, 'empty link child text node is empty' );
		$this->assertSame( 1, $imageLink->childNodes->length, 'image link has one child after fixer' );
		$this->assertSame( 0, $imageNode->childNodes->length, 'img has no children after fixer' );
	}
}
