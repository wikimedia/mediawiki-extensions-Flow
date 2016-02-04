<?php

namespace Flow\Tests\Api;

use Title;
use User;
use WatchedItem;
use WatchedItemStore;

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
				function( User $user, Title $title ) {
					if( class_exists( 'WatchedItemStore' ) ) {
						WatchedItemStore::getDefaultInstance()->removeWatch( $user, $title->getSubjectPage() );
						WatchedItemStore::getDefaultInstance()->removeWatch( $user, $title->getTalkPage() );
					} else {
						WatchedItem::fromUserTitle( $user, $title )->removeWatch();
					}
				},
				// extra request parameters
				array(),
			),
			array(
				'Unwatch a topic',
				// expected key in api result
				'unwatched',
				// initialization
				function( User $user, Title $title ) {
					WatchedItemStore::getDefaultInstance()->addWatch( $user, $title );
					if( class_exists( 'WatchedItemStore' ) ) {
						WatchedItemStore::getDefaultInstance()->addWatch( $user, $title->getSubjectPage() );
						WatchedItemStore::getDefaultInstance()->addWatch( $user, $title->getTalkPage() );
					} else {
						WatchedItem::fromUserTitle( $user, $title )->removeWatch();
					}
				},
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
		$init( self::$users['sysop']->getUser(), $title );

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
