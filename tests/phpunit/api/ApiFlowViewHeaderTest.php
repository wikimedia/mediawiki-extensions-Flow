<?php

namespace Flow\Tests\Api;

use Flow\Container;
use FlowHooks;
use Title;
use User;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowViewHeaderTest extends ApiTestCase {

	protected $tablesUsed = array( 'flow_revision' );

	public function testViewEmptyHeader() {
		$data = $this->doApiRequest( array(
			'page' => "Talk:Flow_QA",
			'action' => 'flow',
			'submodule' => 'view-header',
		) );

		$result = $data[0]['flow']['view-header']['result']['header'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $debug );

		// a revision key should exist with only an action link
		$this->assertArrayHasKey( 'revision', $result, $debug );
		$revision = $result['revision'];
		$this->assertEmpty( $revision['links'], $debug );
		$this->assertEquals( array( 'edit' ), array_keys( $revision['actions'] ), $debug );
		$this->assertArrayNotHasKey( 'content', $revision );
		$this->assertEquals(
			'flow-board',
			Title::newFromText( 'Talk:Flow_QA' )->getContentModel(),
			'Appropriate content model must be set after performing header edit to blank page'
		);
	}

	public function testViewHeader() {
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow_QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-header',
			'ehprev_revision' => '',
			'ehcontent' => 'swimmingly',
		) );
		$result = $data[0]['flow']['edit-header']['result']['header'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $debug );

		$data = $this->doApiRequest( array(
			'page' => "Talk:Flow_QA",
			'action' => 'flow',
			'submodule' => 'view-header',
		) );
		$result = $data[0]['flow']['view-header']['result']['header'];
		$debug = json_encode( $result );
		$this->assertArrayHasKey( 'errors', $result, $debug );
		$this->assertCount( 0, $result['errors'], $debug );
		$this->assertArrayHasKey( 'revision', $result );

		$revision = $result['revision'];
		$this->assertArrayHasKey( 'revisionId', $revision, $debug );
		$this->assertArrayHasKey( 'content', $revision, $debug );
		$this->assertArrayHasKey( 'content', $revision['content'], $debug );
		$this->assertEquals(
			'swimmingly',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertArrayHasKey( 'format', $revision['content'], $debug );
		$this->assertEquals( 'html', $revision['content']['format'], $debug );
	}

	/**
	 * @todo
	 *
	public function testViewHistorical() {
	}
	 */
}
