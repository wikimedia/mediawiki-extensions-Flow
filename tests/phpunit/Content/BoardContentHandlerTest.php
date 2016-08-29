<?php

namespace Flow\Tests\Content;

use MediaWikiTestCase;

use Flow\Content\BoardContentHandler;
use Flow\FlowMessagePoster;

class BoardContentHandlerTest extends MediaWikiTestCase {
	public function testGetMessagePoster() {
		$contentHandler = new BoardContentHandler( CONTENT_MODEL_FLOW_BOARD );
		$messagePoster = $contentHandler->getMessagePoster();

		$this->assertInstanceOf(
			FlowMessagePoster::class,
			$messagePoster,
			'FlowMessagePoster is constructed and returned by BoardContentHandler'
		);
	}
}
