<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditHeaderTest extends ApiTestCase {
	public function testEditHeader() {
		$data = $this->doApiRequest( array(
			'page' => "Talk:Flow_QA",
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-header',
			'ehprev_revision' => '',
			'ehcontent' => '(._.)',
			'ehformat' => 'html',
		) );

		$result = $data[0]['flow']['edit-header']['result']['header'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $result );

		$this->assertArrayHasKey( 'revision', $result, $debug );
		$revision = $result['revision'];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'create-header', $revision['changeType'], $debug );
		$this->assertEquals(
			'(._.)',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'html', $revision['content']['format'], $debug );
	}
}
