<?php

namespace Flow\Tests\Api;

use Title;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiWatchTopicTest extends ApiTestCase {

	public function watchTopicProvider() {
		return [
			[
				'Watch a topic',
				// expected key in api result
				'watched',
				// initialization
				function ( User $user, Title $title ) {
					$user->removeWatch( $title, false );
				},
				// extra request parameters
				[],
			],
			[
				'Unwatch a topic',
				// expected key in api result
				'unwatched',
				// initialization
				function ( User $user, Title $title ) {
					$user->addWatch( $title, false );
				},
				// extra request parameters
				[ 'unwatch' => 1 ],
			],
		];
	}

	/**
	 * @dataProvider watchTopicProvider
	 */
	public function testWatchTopic( $message, $expect, $init, array $request ) {
		$topic = $this->createTopic();

		$title = Title::newFromText( $topic['topic-page'] );
		$init( self::$users['sysop']->getUser(), $title );

		// issue a watch api request
		$data = $this->doApiRequest( $request + [
				'action' => 'watch',
				'format' => 'json',
				'titles' => $topic['topic-page'],
				'token' => $this->getEditToken( null, 'watchtoken' ),
		] );
		$this->assertArrayHasKey( $expect, $data[0]['watch'][0], $message );
	}
}
