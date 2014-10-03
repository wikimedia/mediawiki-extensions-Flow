<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowLockTopicTest extends \ApiTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = array( 'flow_revision', 'flow_tree_revision' );

	protected function setUp() {
		$this->setMwGlobals( 'wgFlowOccupyPages', array( 'Talk:Flow QA' ) );
		parent::setUp();
	}

	protected function getEditToken() {
		$tokens = $this->getTokenList( self::$users['sysop'] );
		return $tokens['edittoken'];
	}

	protected function doApiRequest(
		array $params,
		array $session = null,
		$appendModule = false,
		User $user = null
	) {
		// reset flow state before each request
		FlowHooks::resetFlowExtension();
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user ?: self::$users['sysop']->user;
		return parent::doApiRequest( $params, $session, $appendModule, $user );
	}

	public function testLockTopic() {
		$workflowId = $this->createTopic();
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'lock',
			'cotreason' => 'fiddle faddle',
			'cotprev_revision' => null,
		) );

		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertCount( 0, $result['errors'] );
		$this->assertArrayHasKey( 'workflowId', $result );
		$this->assertEquals( $workflowId, $result['workflowId'] );
		$this->assertArrayHasKey( 'changeType', $result );
		$this->assertEquals( 'lock-topic', $result['changeType'] );
		$this->assertArrayHasKey( 'isModerated', $result );
		$this->assertTrue( $result['isModerated'] );
		$this->assertArrayHasKey( 'actions', $result );
		$this->assertArrayHasKey( 'unlock', $result['actions'] );
		$this->assertArrayHasKey( 'moderateReason', $result );
		$this->assertEquals( 'fiddle faddle', $result['moderateReason']['content'] );
		$this->assertEquals( 'plaintext', $result['moderateReason']['format'] );
	}

	public function testUnlockTopic() {
		$workflowId = $this->createTopic();
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'lock',
			'cotreason' => 'fiddle faddle',
		) );
		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertCount( 0, $result['errors'] );

		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'unlock',
			'cotreason' => 'Ether',
		) );

		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertCount( 0, $result['errors'] );
		$this->assertArrayHasKey( 'changeType', $result );
		$this->assertEquals( 'restore-topic', $result['changeType'] );
		$this->assertArrayHasKey( 'isModerated', $result );
		$this->assertFalse( $result['isModerated'] );
		$this->assertArrayHasKey( 'actions', $result );
		$this->assertArrayHasKey( 'lock', $result['actions'] );
		// Is this intentional? We don't display it by default
		// but perhaps it should still be in the api output.
		$this->assertArrayNotHasKey( 'moderateReason', $result );
	}

	protected function createTopic() {
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'new-topic',
			'nttopic' => 'Hi there!',
			'ntcontent' => '...',
		) );
		$this->assertTrue(
			// @todo we should return the new id much more directly than this
			isset( $data[0]['flow']['new-topic']['result']['topiclist']['roots'][0] ),
			'Api response must contain new topic id'
		);

		return $data[0]['flow']['new-topic']['result']['topiclist']['roots'][0];
	}
}
