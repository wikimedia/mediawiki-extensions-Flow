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
		$topic = $this->createTopic();

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-topic-summary',
			'etsprev_revision' => '',
			'etssummary' => '( ●_●)-((⌼===((() ≍≍≍≍≍ ♒ ✺ ♒ ZAP!',
			'etsformat' => 'wikitext',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['edit-topic-summary']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['edit-topic-summary']['committed'], $debug );

		$data = $this->doApiRequest( array(
			'page' => $topic['topic-page'],
			'action' => 'flow',
			'submodule' => 'view-topic-summary',
			'vtsformat' => 'html',
		) );

		$debug = json_encode( $data );
		$revision = $data[0]['flow']['view-topic-summary']['result']['topicsummary']['revision'];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'create-topic-summary', $revision['changeType'], $debug );
		$this->assertEquals(
			'( ●_●)-((⌼===((() ≍≍≍≍≍ ♒ ✺ ♒ ZAP!',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'html', $revision['content']['format'], $debug );
	}
}
