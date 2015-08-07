<?php

namespace Flow\Tests\Api;

use Title;
use WatchedItem;

/**
 * @group Flow
 * @group medium
 */
class ApiWatchTopicTest extends ApiTestCase {

	public function watchTopicProvider() {
		return array(
			array(
				'Watch a topic',
				// expected key in api result
				'watched',
				// initialization
				function( WatchedItem $item ) { $item->removeWatch(); },
				// extra request parameters
				array(),
			),
			array(
				'Unwatch a topic',
				// expected key in api result
				'unwatched',
				// initialization
				function( WatchedItem $item ) { $item->addWatch(); },
				// extra request parameters
				array( 'unwatch' => 1 ),
			),
		);
	}

	/**
	 * @dataProvider watchTopicProvider
	 */
	public function testWatchTopic( $message, $expect, $init, array $request ) {
		$topic = $this->createTopic();

		$title = Title::newFromText( $topic['topic-page'] );
		$init( WatchedItem::fromUserTitle( self::$users['sysop']->getUser(), $title, false ) );

		// issue a watch api request
		$data = $this->doApiRequest( $request + array(
				'action' => 'watch',
				'format' => 'json',
				'titles' => $topic['topic-page'],
				'token' => $this->getEditToken( null, 'watchtoken' ),
		) );
		$this->assertArrayHasKey( $expect, $data[0]['watch'][0], $message );
	}
}
