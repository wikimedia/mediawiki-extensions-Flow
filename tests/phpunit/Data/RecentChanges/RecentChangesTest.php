<?php

namespace Flow\Tests\Data\RecentChanges;

use Flow\Container;
use Flow\Data\RecentChanges\RecentChanges;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Title;
use User;

/**
 * @group Flow
 */
class RecentChangesTest extends \MediaWikiTestCase {

	public function somethingProvider() {
		return array(
			array(
				'New topic recent change goes to the board',
				// expect
				NS_MAIN,
				// something
				function( $workflow ) {
					return PostRevision::create( $workflow, 'blah blah' );
				}
			),

			array(
				'Reply recent change goes to the topic',
				NS_TOPIC,
				function( $workflow ) {
					$first = PostRevision::create( $workflow, 'blah blah' );
					$user = $workflow->getUserTuple()->createUser();
					return $first->reply( $workflow, $user, 'fofofo' );
				},
			),
		);
	}

	/**
	 * @dataProvider somethingProvider
	 */
	public function testSomething( $message, $expect, $init ) {
		$actions = Container::get( 'flow_actions' );
		$usernames = $this->getMockBuilder( 'Flow\Repository\UserNameBatch' )
			->disableOriginalConstructor()
			->getMock();
		$rcFactory = $this->getMockBuilder( 'Flow\Data\Utils\RecentChangeFactory' )
			->disableOriginalConstructor()
			->getMock();

		$rc = new RecentChanges( $actions, $usernames, $rcFactory );
		$change = $this->getMock( 'RecentChange' );
		$rcFactory->expects( $this->once() )
			->method( 'newFromRow' )
			->will( $this->returnCallback( function( $obj ) use ( &$ref, $change ) {
				$ref = $obj;
				return $change;
			} ) );

		$title = Title::newMainPage();
		$user = User::newFromName( '127.0.0.1', false );
		$workflow = Workflow::create( 'topic', $user, $title );

		$revision = $init( $workflow );

		$rc->onAfterInsert(
			$revision,
			array( 'rev_user_id' => 0, 'rev_user_ip' => '127.0.0.1' ),
			array( 'workflow' => $workflow )
		);
		$this->assertNotNull( $ref );
		$this->assertEquals( $expect, $ref->rc_namespace, $message );
	}
}
