<?php

namespace Flow\Tests\Parsoid;

use Flow\Parsoid\BadImageRemover;
use Flow\Parsoid\Utils;
use Title;

class BadImageRemoverTest extends \MediaWikiTestCase {

	public static function imageRemovalProvider() {
		return array(
			array(
				'Passes through allowed good images',
				// expected wikitext after filtering
				'[[File:Image.jpg]] and other stuff',
				// input wikitext
				'[[File:Image.jpg]] and other stuff',
				// accept/decline callback
				function() { return false; }
			),

			array(
				'Keeps unknown images',
				// expected wikitext after filtering
				"[[File:Doe/s/not/exi/st.jpg]]and content",
				// input wikitext
				'[[File:Doe/s/not/exi/st.jpg]]and content',
				// accept/decline callback
				function() { return true; }
			),

			array(
				'Strips declined images',
				// expected wikitext after filtering
				'and other stuff',
				// input wikitex
				'[[File:Image.jpg]]and other stuff',
				// accept/decline callback
				function() { return true; }
			),
		);
	}
	/**
	 * @dataProvider imageRemovalProvider
	 */
	public function testImageRemoval( $message, $expect, $wikitext, $badImageFilter ) {
		$fixer = new BadImageRemover( $badImageFilter );
		$title = Title::newMainPage();
		$content = Utils::convert( 'wt', 'html', $wikitext, $title );
		// Note that the real use case involves outputing the html directly
		// rather than converting back to wikitext, but the test is easier
		// to write this way.
		$result = Utils::convert( 'html', 'wt', $fixer->apply( $content, $title ), $title );
		$this->assertEquals( $expect, trim( $result ), $message );
	}
}
