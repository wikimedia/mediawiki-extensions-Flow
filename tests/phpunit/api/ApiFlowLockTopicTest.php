<?php

namespace Flow\Tests\Api;

use Flow\Container;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowLockTopicTest extends \ApiTestCase {
	protected function setUp() {
		$this->setMwGlobals( 'wgFlowOccupyNamespaces', NS_TALK );
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
			'cotsummary' => 'fiddle faddle',
			'cotprev_revision' => null,
		) );

		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertCount( 0, $result['errors'] );
		$this->assertEquals( $workflowId, $result['workflowId'] );
		$this->assertEquals( 'lock-topic', $result['changeType'] );
		$this->assertTrue( $result['isModerated'] );
		$this->assertArrayHasKey( 'unlock', $result['actions'] );
		$this->assertEquals( 'fiddle faddle', $result['moderateReason'] );
	}

	public function testUnlockTopic() {
		$workflowId = $this->createTopic();
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'lock',
			'cotsummary' => 'fiddle faddle',
			'cotprev_revision' => null,
		) );
		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertCount( 0, $result['errors'] );

		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'unlock',
			'cotsummary' => 'Ether',
			'cotprev_revision' => $result['summary']['revId'],
		) );

		$result = $data[0]['flow']['lock-topic']['result']['topic'];
		$this->assertCount( 0, $result['errors'] );
		$this->assertEquals( 'restore-topic', $result['changeType'] );
		$this->assertFalse( $result['isModerated'] );
		$this->assertArrayHasKey( 'lock', $result['actions'] );
		$this->assertEquals(
			'<p data-parsoid=\'{"dsr":[0,5,0,0]}\'>Ether</p>',
			$result['summary']['content']
		);
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
