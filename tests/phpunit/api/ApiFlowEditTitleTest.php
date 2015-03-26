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
		$result = $this->createTopic( 'result' );
		$workflowId = $result['roots'][0];
		$revisionId = $result['posts'][$workflowId][0];
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-title',
			'etprev_revision' => $revisionId,
			'etcontent' => '(ﾉ◕ヮ◕)ﾉ*:･ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ✧'
		) );

		$result = $data[0]['flow']['edit-title']['result']['topic'];

		$this->assertArrayHasKey( 'errors', $result );
		$this->assertCount( 0, $result['errors'], json_encode( $result['errors'] ) );

		$revisionId = $result['posts'][$workflowId][0];
		$revision = $result['revisions'][$revisionId];
		$debug = json_encode( $revision );
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'edit-title', $revision['changeType'], $debug );
		$this->assertEquals( '(ﾉ◕ヮ◕)ﾉ*:･ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ ﾟ✧', $revision['content']['content'], $debug );
		$this->assertEquals( 'plaintext', $revision['content']['format'], $debug );
	}
}
