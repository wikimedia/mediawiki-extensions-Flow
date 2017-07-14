<?php

namespace Flow\Tests\Api;

use Sanitizer;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditPostTest extends ApiTestCase {
	public function testEditPost() {
		$topic = $this->createTopic();

		$data = $this->doApiRequest( [
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-post',
			'eppostId' => $topic['post-id'],
			'epprev_revision' => $topic['post-revision-id'],
			'epcontent' => '⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			'epformat' => 'wikitext',
		] );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['edit-post']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['edit-post']['committed'], $debug );

		$replyPostId = $data[0]['flow']['edit-post']['committed']['topic']['post-id'];
		$replyRevisionId = $data[0]['flow']['edit-post']['committed']['topic']['post-revision-id'];

		$data = $this->doApiRequest( [
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-post',
			'vppostId' => $replyPostId,
			'vpformat' => 'html',
		] );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-post']['result']['topic']['revisions'][$replyRevisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'edit-post', $revision['changeType'], $debug );
		$this->assertEquals(
			'⎛ ﾟ∩ﾟ⎞⎛ ⍜⌒⍜⎞⎛ ﾟ⌒ﾟ⎞',
			trim( Sanitizer::stripAllTags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'html', $revision['content']['format'], $debug );
	}
}
