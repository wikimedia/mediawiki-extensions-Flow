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
		$topic = $this->createTopic();

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'reply',
			'repreplyTo' => $topic['topic-id'],
			'repcontent' => '⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			'repformat' => 'wikitext',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['reply']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['reply']['committed'], $debug );

		$replyPostId = $data[0]['flow']['reply']['committed']['topic']['post-id'];
		$replyRevisionId = $data[0]['flow']['reply']['committed']['topic']['post-revision-id'];

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-post',
			'vppostId' => $replyPostId,
			'vpformat' => 'html',
		) );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-post']['result']['topic']['revisions'][$replyRevisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'reply', $revision['changeType'], $debug );
		$this->assertEquals(
			'⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'html', $revision['content']['format'], $debug );
	}
}
