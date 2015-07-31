<?php

namespace Flow\Tests\Formatter;

use Flow\Container;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\UUID;
use Flow\Tests\FlowTestCase;
use Flow\UrlGenerator;
use Title;

/**
 * @group Flow
 */
class FormatterTest extends FlowTestCase {

	static public function checkUserProvider() {
		$topicId = UUID::create();
		$revId = UUID::create();
		$postId = UUID::create();

		return array(
			array(
				'With only a topicId reply should not fail',
				// result must contain
				function( $test, $message, $result ) {
					$test->assertNotNull( $result );
					$test->assertArrayHasKey( 'links', $result, $message );
				},
				// cuc_comment parameters
				'reply', $topicId, $revId, null
			),

			array(
				'With topicId and postId should not fail',
				function( $test, $message, $result ) {
					$test->assertNotNull( $result );
					$test->assertArrayHasKey( 'links', $result, $message );
				},
				'reply', $topicId, $revId, $postId,
			),
		);
	}

	/**
	 * @dataProvider checkUserProvider
	 */
	public function testCheckUserFormatter( $message, $test, $action, UUID $workflowId, UUID $revId, UUID $postId = null ) {
		global $wgLang;

		if ( !class_exists( 'CheckUser' ) ) {
			$this->markTestSkipped( 'CheckUser is not available' );
			return;
		}

		$title = Title::newFromText( 'Test', NS_USER_TALK );
		$row = new FormatterRow;
		$row->workflow = $this->mockWorkflow( $workflowId, $title );
		$row->revision = $this->mockRevision( $action, $revId, $postId );
		$row->currentRevision = $row->revision;

		$ctx = $this->getMock( 'IContextSource' );
		$ctx->expects( $this->any() )
			->method( 'getLanguage' )
			->will( $this->returnValue( $wgLang ) );
		$ctx->expects( $this->any() )
			->method( 'msg' )
			->will( $this->returnCallback( 'wfMessage' ) );

		// Code uses wfWarn as a louder wfDebugLog in error conditions.
		// but phpunit considers a warning a fail.
		wfSuppressWarnings();
		$links = $this->createFormatter( 'Flow\Formatter\CheckUserFormatter' )->format( $row, $ctx );
		wfRestoreWarnings();
		$test( $this, $message, $links );
	}

	protected function mockWorkflow( UUID $workflowId, Title $title ) {
		$workflow = $this->getMock( 'Flow\\Model\\Workflow' );
		$workflow->expects( $this->any() )
			->method( 'getId' )
			->will( $this->returnValue( $workflowId ) );
		$workflow->expects( $this->any() )
			->method( 'getArticleTitle' )
			->will( $this->returnValue( $title ) );
		return $workflow;
	}

	protected function mockRevision( $changeType, UUID $revId, UUID $postId = null ) {
		if ( $postId ) {
			$revision = $this->getMock( 'Flow\\Model\\PostRevision' );
		} else {
			$revision = $this->getMock( 'Flow\\Model\\Header' );
		}
		$revision->expects( $this->any() )
			->method( 'getChangeType' )
			->will( $this->returnValue( $changeType ) );
		$revision->expects( $this->any() )
			->method( 'getRevisionId' )
			->will( $this->returnValue( $revId ) );
		if ( $postId ) {
			$revision->expects( $this->any() )
				->method( 'getPostId' )
				->will( $this->returnValue( $postId ) );
		}
		return $revision;
	}

	protected function createFormatter( $class ) {
		$permissions = $this->getMockBuilder( 'Flow\RevisionActionPermissions' )
			->disableOriginalConstructor()
			->getMock();
		$permissions->expects( $this->any() )
			->method( 'isAllowed' )
			->will( $this->returnValue( true ) );
		$permissions->expects( $this->any() )
			->method( 'getActions' )
			->will( $this->returnValue( Container::get( 'flow_actions' ) ) );

		$templating = $this->getMockBuilder( 'Flow\Templating' )
			->disableOriginalConstructor()
			->getMock();
		$workflowMapper = $this->getMockBuilder( 'Flow\Data\Mapper\CachingObjectMapper' )
			->disableOriginalConstructor()
			->getMock();
		$urlGenerator = new UrlGenerator( $workflowMapper );
		$templating->expects( $this->any() )
			->method( 'getUrlGenerator' )
			->will( $this->returnValue( $urlGenerator ) );

		$usernames = $this->getMockBuilder( 'Flow\Repository\UserNameBatch' )
			->disableOriginalConstructor()
			->getMock();

		global $wgFlowMaxThreadingDepth;
		$serializer = new RevisionFormatter( $permissions, $templating, $usernames, $wgFlowMaxThreadingDepth );

		return new $class( $permissions, $serializer );
	}

	protected function dataToString( $data ) {
		foreach ( $data as $key => $value ) {
			if ( $value instanceof UUID ) {
				$data[$key] = "UUID: " . $value->getAlphadecimal();
			}
		}
		return parent::dataToString( $data );
	}
}
