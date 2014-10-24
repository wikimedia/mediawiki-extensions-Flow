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
				'Newly created topic inserts as board',
				// expect
				NS_MAIN,
				// something
				function( $workflow, $user ) {
					return PostRevision::create( $workflow, $user, 'blah blah' );
				}
			),

			array(
				'Replies go to the topic',
				NS_TOPIC,
				function( $workflow, $user ) {
					$first = PostRevision::create( $workflow, $user, 'blah blah' );
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
		$rcFactory = $this->getMockBuilder( 'Flow\Data\RecentChanges\RecentChangeFactory' )
			->disableOriginalConstructor()
			->getMock();

		$rc = new RecentChangesMock( $actions, $usernames, $rcFactory );
		$change = $this->getMock( 'RecentChange' );
		$rcFactory->expects( $this->once() )
			->method( 'newFromRow' )
			->will( $this->returnCallback( function( $obj ) use ( &$ref, $change ) {
				$ref = $obj;
				return $change;
			} ) );

		$title = Title::newMainPage();
		$user = User::newFromName( '127.0.0.1', false );
		$workflow = Workflow::create( 'topic', $title );

		$revision = $init( $workflow, $user );

		$rc->onAfterInsert( $revision, array(), array(
			'workflow' => $workflow,
		) );
		$this->assertNotNull( $ref );
		$this->assertEquals( $expect, $ref->rc_namespace, $message );
	}
}

class RecentChangesMock extends RecentChanges {
	// Mock abuses metadata parameter to test parent class
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$metadata += array(
			'block' => null,
			'revisionType' => null,
			'row' => array(),
			'workflow' => null, // @todo real workflow is required
			'changes' => array()
		);

		$this->insert(
			$object,
			$metadata['block'],
			$metadata['revisionType'],
			$metadata['row'] + array(
				'rev_user_id' => 0,
				'rev_user_ip' => '127.0.0.1',
			),
			$metadata['workflow'],
			$metadata['changes']
		);
	}
}
