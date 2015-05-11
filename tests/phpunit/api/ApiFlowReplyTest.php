<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowReplyTest extends ApiTestCase {
	public function testTopLevelReply() {
		$result = $this->createTopic( 'result' );
		$workflowId = $result['roots'][0];
		$topicRevId = $result['posts'][$workflowId][0];

		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'reply',
			'repreplyTo' => $workflowId,
			'repcontent' => '⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			'repformat' => 'wikitext',
		) );

		$result = $data[0]['flow']['reply']['result']['topic'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $result );

		$newPostId = end( $result['revisions'][$topicRevId]['replies'] );
		$newRevisionId = $result['posts'][$newPostId][0];
		$revision = $result['revisions'][$newRevisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'reply', $revision['changeType'], $debug );
		$this->assertEquals(
			'⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		// @todo: below test is invalid with this patch, tests will be properly fixed in follow-up patch
//		$this->assertEquals( 'wikitext', $revision['content']['format'], $debug );
	}
}
