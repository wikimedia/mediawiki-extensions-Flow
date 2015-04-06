<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditTopicSummaryTest extends ApiTestCase {
	public function testEditTopicSummary() {
		$workflowId = $this->createTopic();
		$data = $this->doApiRequest( array(
			'page' => "Topic:$workflowId",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-topic-summary',
			'etsprev_revision' => '',
			'etssummary' => '( ●_●)-((⌼===((() ≍≍≍≍≍ ♒ ✺ ♒ ZAP!',
			'etsformat' => 'wikitext',
		) );

		$result = $data[0]['flow']['edit-topic-summary']['result']['topicsummary'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $result );

		$revision = $result['revision'];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'create-topic-summary', $revision['changeType'], $debug );
		$this->assertEquals(
			'( ●_●)-((⌼===((() ≍≍≍≍≍ ♒ ✺ ♒ ZAP!',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'wikitext', $revision['content']['format'], $debug );
	}
}
