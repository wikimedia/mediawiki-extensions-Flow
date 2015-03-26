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
class ApiFlowModeratePostTest extends ApiTestCase {
	public function testModeratePost() {
		$result = $this->createTopic( 'result' );
		$workflowId = $result['roots'][0];
		$topicRevisionId = $result['posts'][$workflowId][0];
		$topic = $result['revisions'][$topicRevisionId];
		$replyPostId = $topic['replies'][0];

		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'moderate-post',
			'mpmoderationState' => AbstractRevision::MODERATED_HIDDEN,
			'mppostId' => $replyPostId,
			'mpreason' => '<>&{};'
		) );

		$result = $data[0]['flow']['moderate-post']['result']['topic'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertCount( 0, $result['errors'], json_encode( $result['errors'] ) );

		$newRevisionId = $result['posts'][$replyPostId][0];
		$revision = $result['revisions'][$newRevisionId];
		$debug = json_encode( $revision );
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'hide-post', $revision['changeType'], $debug );
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
