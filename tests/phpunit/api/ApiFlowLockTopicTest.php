<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowLockTopicTest extends ApiTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = array(
		'flow_workflow',
		'flow_revision',
		'flow_tree_revision',
		'flow_tree_node',
		'flow_topic_list',
	);

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
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $debug );
		$this->assertArrayHasKey( 'workflowId', $result, $debug );
		$this->assertEquals( $workflowId, $result['workflowId'], $debug );
		$this->assertArrayHasKey( 'changeType', $result, $debug );
		$this->assertEquals( 'lock-topic', $result['changeType'], $debug );
		$this->assertArrayHasKey( 'isModerated', $result, $debug );
		$this->assertTrue( $result['isModerated'], $debug );
		$this->assertArrayHasKey( 'actions', $result, $debug );
		$this->assertArrayHasKey( 'unlock', $result['actions'], $debug );
		$this->assertArrayHasKey( 'moderateReason', $result, $debug );
		$this->assertEquals( 'fiddle faddle', $result['moderateReason']['content'], $debug );
		$this->assertEquals( 'plaintext', $result['moderateReason']['format'], $debug );
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
}
