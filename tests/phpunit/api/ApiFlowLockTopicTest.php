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
	public function testLockTopic() {
		$topic = $this->createTopic();

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'lock',
			'cotreason' => 'fiddle faddle',
			'cotprev_revision' => null,
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['lock-topic']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['lock-topic']['committed'], $debug );

		$revisionId = $data[0]['flow']['lock-topic']['committed']['topic']['post-revision-id'];

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-topic',
			'vpformat' => 'html',
		) );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-topic']['result']['topic']['revisions'][$revisionId];
		$this->assertArrayHasKey( 'workflowId', $revision, $debug );
		$this->assertEquals( $topic['topic-id'], $revision['workflowId'], $debug );
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'lock-topic', $revision['changeType'], $debug );
		$this->assertArrayHasKey( 'isModerated', $revision, $debug );
		$this->assertTrue( $revision['isModerated'], $debug );
		$this->assertArrayHasKey( 'actions', $revision, $debug );
		$this->assertArrayHasKey( 'unlock', $revision['actions'], $debug );
		$this->assertArrayHasKey( 'moderateReason', $revision, $debug );
		$this->assertEquals( 'fiddle faddle', $revision['moderateReason']['content'], $debug );
		$this->assertEquals( 'plaintext', $revision['moderateReason']['format'], $debug );
	}

	public function testUnlockTopic() {
		$topic = $this->createTopic();

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'lock',
			'cotreason' => 'fiddle faddle',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['lock-topic']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['lock-topic']['committed'], $debug );

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'lock-topic',
			'cotmoderationState' => 'unlock',
			'cotreason' => 'Ether',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['lock-topic']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['lock-topic']['committed'], $debug );

		$revisionId = $data[0]['flow']['lock-topic']['committed']['topic']['post-revision-id'];

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-topic',
			'vpformat' => 'html',
		) );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-topic']['result']['topic']['revisions'][$revisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'restore-topic', $revision['changeType'], $debug );
		$this->assertArrayHasKey( 'isModerated', $revision, $debug );
		$this->assertFalse( $revision['isModerated'], $debug );
		$this->assertArrayHasKey( 'actions', $revision, $debug );
		$this->assertArrayHasKey( 'lock', $revision['actions'], $debug );
		// Is this intentional? We don't display it by default
		// but perhaps it should still be in the api output.
		$this->assertArrayNotHasKey( 'moderateReason', $revision, $debug );
	}
}
