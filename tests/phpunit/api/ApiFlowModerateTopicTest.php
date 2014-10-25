<?php

namespace Flow\Tests\Api;

use Flow\Container;
use Flow\Model\AbstractRevision;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowModerateTopicTest extends ApiTestCase {

	protected $tablesUsed = array( 'flow_revision' );

	public function testModerateTopic() {
		$workflowId = $this->createTopic();
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'moderate-topic',
			'mtmoderationState' => AbstractRevision::MODERATED_HIDDEN,
			'mtreason' => '<>&{};'
		) );

		$result = $data[0]['flow']['moderate-topic']['result']['topic'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertCount( 0, $result['errors'], json_encode( $result['errors'] ) );

		$newRevisionId = $result['posts'][$workflowId][0];
		$revision = $result['revisions'][$newRevisionId];
		$debug = json_encode( $revision );
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'hide-topic', $revision['changeType'], $debug );
		$this->assertArrayHasKey( 'isModerated', $revision, $debug );
		$this->assertTrue( $revision['isModerated'], $debug );
		$this->assertArrayHasKey( 'actions', $revision, $debug );
		$this->assertArrayHasKey( 'unhide', $revision['actions'], $debug );
		$this->assertArrayHasKey( 'moderateState', $revision, $debug );
		$this->assertEquals( AbstractRevision::MODERATED_HIDDEN, $revision['moderateState'], $debug );
		$this->assertArrayHasKey( 'moderateReason', $revision, $debug );
		$this->assertArrayHasKey( 'content', $revision['moderateReason'], $debug );
		$this->assertEquals( '<>&{};', $revision['moderateReason']['content'], $debug );
		$this->assertArrayHasKey( 'format', $revision['moderateReason'], $debug );
		$this->assertEquals( 'plaintext', $revision['moderateReason']['format'], $debug );
	}
}
