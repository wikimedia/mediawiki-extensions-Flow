<?php

namespace Flow\Tests\Api;

/**
 * @group Flow
 * @group medium
 */
class ApiFlowEditHeaderTest extends ApiTestCase {
	public function testEditHeader() {

		// create header
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow_QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-header',
			'ehprev_revision' => '',
			'ehcontent' => '(._.)',
			'ehformat' => 'wikitext',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['edit-header']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['edit-header']['committed'], $debug );

		// get header
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow_QA',
			'action' => 'flow',
			'submodule' => 'view-header',
			'vhformat' => 'html',
		) );

		$debug = json_encode( $data );
		$result = $data[0]['flow']['view-header']['result']['header'];
		$this->assertArrayHasKey( 'revision', $result, $debug );
		$revision = $result['revision'];
		$revisionId = $revision['revisionId'];
		$this->assertArrayHasKey( 'changeType', $revision, $debug );
		$this->assertEquals( 'create-header', $revision['changeType'], $debug );
		$this->assertEquals(
			'(._.)',
			trim( strip_tags( $revision['content']['content'] ) ),
			$debug
		);
		$this->assertEquals( 'html', $revision['content']['format'], $debug );

		// update header (null edit)
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow_QA',
			'token' => $this->getEditToken(),
			'action' => 'flow',
			'submodule' => 'edit-header',
			'ehprev_revision' => $revisionId,
			'ehcontent' => '(._.)',
			'ehformat' => 'wikitext',
		) );

		$debug = json_encode( $data );
		$this->assertEquals( 'ok', $data[0]['flow']['edit-header']['status'], $debug );
		$this->assertCount( 1, $data[0]['flow']['edit-header']['committed'], $debug );

		// get header again
		$data = $this->doApiRequest( array(
			'page' => 'Talk:Flow_QA',
			'action' => 'flow',
			'submodule' => 'view-header',
			'vhformat' => 'html',
		) );

		$newRevisionId = $data[0]['flow']['view-header']['result']['header']['revision']['revisionId'];

		$this->assertEquals( $revisionId, $newRevisionId, $debug );
	}
}
