<?php

namespace Flow\Tests\Data\Listener;

use Flow\Container;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Data\Utils\RecentChangeFactory;
use Flow\Formatter\IRCLineUrlFormatter;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Repository\UserNameBatch;
use Title;
use User;

/**
 * @covers \Flow\Data\Listener\AbstractListener
 * @covers \Flow\Data\Listener\RecentChangesListener
 *
 * @group Flow
 */
class RecentChangesListenerTest extends \MediaWikiIntegrationTestCase {

	public function somethingProvider() {
		return [
			[
				'Reply recent change goes to the topic',
				NS_TOPIC,
				static function ( $workflow, $user ) {
					$first = PostRevision::createTopicPost( $workflow, $user, 'blah blah' );
					return $first->reply( $workflow, $user, 'fofofo', 'wikitext' );
				},
			],
		];
	}

	/**
	 * @dataProvider somethingProvider
	 */
	public function testSomething( $message, $expect, $init ) {
		$rcFactory = $this->createMock( RecentChangeFactory::class );
		$rcFactory->expects( $this->once() )
			->method( 'newFromRow' )
			->willReturnCallback( function ( $obj ) use ( &$ref ) {
				$ref = $obj;
				return $this->createMock( \RecentChange::class );
			} );

		$rc = new RecentChangesListener(
			Container::get( 'flow_actions' ),
			$this->createMock( UserNameBatch::class ),
			$rcFactory,
			$this->createMock( IRCLineUrlFormatter::class )
		);

		$user = User::newFromName( '127.0.0.1', false );
		$workflow = Workflow::create( 'topic', Title::newMainPage() );

		$revision = $init( $workflow, $user );

		$rc->onAfterInsert(
			$revision,
			[ 'rev_user_id' => 0, 'rev_user_ip' => '127.0.0.1' ],
			[ 'workflow' => $workflow ]
		);
		$this->assertNotNull( $ref );
		$this->assertEquals( $expect, $ref->rc_namespace, $message );
	}
}
