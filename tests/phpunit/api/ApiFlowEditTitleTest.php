<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditTitleTest extends ApiTestCase {
	public function testEditTitle() {
		$topic = $this->createTopic();

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-title',
			'etprev_revision' => $topic['topic-revision-id'],
			'etcontent' => '(ﾉ◕ヮ◕)ﾉ*:･ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ✧'
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['edit-title']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['edit-title']['committed'], $debug );

		$revisionId = $data[0]['flow']['edit-title']['committed']['topic']['post-revision-id'];

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-topic',
			'vpformat' => 'html',
		) );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-topic']['result']['topic']['revisions'][$revisionId];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'edit-title', $revision['changeType'], $debug );
		$this->assertEquals( '(ﾉ◕ヮ◕)ﾉ*:･ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ✧', $revision['content']['content'], $debug );
		$this->assertEquals( 'plaintext', $revision['content']['format'], $debug );
	}
}
