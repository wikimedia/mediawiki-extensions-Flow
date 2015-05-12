<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditPostTest extends ApiTestCase {
	public function testEditPost() {
		$result = $this->createTopic( 'result' );
		$workflowId = $result['roots'][0];
		$topicRevisionId = $result['posts'][$workflowId][0];
		$topic = $result['revisions'][$topicRevisionId];

		$replyPostId = $topic['replies'][0];
		$replyRevisionId = $result['posts'][$replyPostId][0];

		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-post',
			'eppostId' => $replyPostId,
			'epprev_revision' => $replyRevisionId,
			'epcontent' => '⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			'epformat' => 'wikitext',
		) );

		$result = $data[0]['flow']['edit-post']['result']['topic'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $result );

		$newRevisionId = $result['posts'][$replyPostId][0];
		$revision = $result['revisions'][$newRevisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'edit-post', $revision['changeType'], $debug );
		$this->assertEquals(
			'⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		// @todo: below test is invalid with this patch, tests will be properly fixed in follow-up patch
//		$this->assertEquals( 'wikitext', $revision['content']['format'], $debug );
	}
}
