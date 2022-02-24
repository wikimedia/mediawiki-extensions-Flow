<?php

namespace Flow\Tests\Formatter;

use ExtensionRegistry;
use Flow\Container;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\UUID;
use Flow\Tests\FlowTestCase;
use Flow\UrlGenerator;
use Title;
use Wikimedia\AtEase\AtEase;

/**
 * @group Flow
 */
class FormatterTest extends FlowTestCase {

	public static function checkUserProvider() {
		$topicId = UUID::create();
		$revId = UUID::create();
		$postId = UUID::create();

		return [
			[
				'With only a topicId reply should not fail',
				// result must contain
				static function ( $test, $message, $result ) {
					$test->assertNotNull( $result );
					$test->assertArrayHasKey( 'links', $result, $message );
				},
				// cuc_comment parameters
				'reply', $topicId, $revId, null
			],

			[
				'With topicId and postId should not fail',
				static function ( $test, $message, $result ) {
					$test->assertNotNull( $result );
					$test->assertArrayHasKey( 'links', $result, $message );
				},
				'reply', $topicId, $revId, $postId,
			],
		];
	}

	/**
	 * @covers \Flow\Formatter\CheckUserFormatter
	 * @dataProvider checkUserProvider
	 * @group Broken
	 */
	public function testCheckUserFormatter( $message, $test, $action, UUID $workflowId, UUID $revId, UUID $postId = null ) {
		global $wgLang;

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'CheckUser' ) ) {
			$this->markTestSkipped( 'CheckUser is not available' );
			return;
		}

		$title = Title::newFromText( 'Test', NS_USER_TALK );
		$row = new FormatterRow;
		$row->workflow = $this->mockWorkflow( $workflowId, $title );
		$row->revision = $this->mockRevision( $action, $revId, $postId );
		$row->currentRevision = $row->revision;

		$ctx = $this->createMock( \IContextSource::class );
		$ctx->method( 'getLanguage' )
			->willReturn( $wgLang );
		$ctx->method( 'msg' )
			->will( $this->returnCallback( 'wfMessage' ) );

		// Code uses wfWarn as a louder wfDebugLog in error conditions.
		// but phpunit considers a warning a fail.
		AtEase::suppressWarnings();
		$links = $this->createFormatter( \Flow\Formatter\CheckUserFormatter::class )->format( $row, $ctx );
		AtEase::restoreWarnings();
		$test( $this, $message, $links );
	}

	protected function mockWorkflow( UUID $workflowId, Title $title ) {
		$workflow = $this->createMock( \Flow\Model\Workflow::class );
		$workflow->method( 'getId' )
			->willReturn( $workflowId );
		$workflow->method( 'getArticleTitle' )
			->willReturn( $title );
		return $workflow;
	}

	protected function mockRevision( $changeType, UUID $revId, UUID $postId = null ) {
		if ( $postId ) {
			$revision = $this->createMock( \Flow\Model\PostRevision::class );
		} else {
			$revision = $this->createMock( \Flow\Model\Header::class );
		}
		$revision->method( 'getChangeType' )
			->willReturn( $changeType );
		$revision->method( 'getRevisionId' )
			->willReturn( $revId );
		if ( $postId ) {
			$revision->method( 'getPostId' )
				->willReturn( $postId );
		}
		return $revision;
	}

	protected function createFormatter( $class ) {
		$permissions = $this->getMockBuilder( \Flow\RevisionActionPermissions::class )
			->disableOriginalConstructor()
			->getMock();
		$permissions->method( 'isAllowed' )
			->willReturn( true );
		$permissions->method( 'getActions' )
			->willReturn( Container::get( 'flow_actions' ) );

		$templating = $this->getMockBuilder( \Flow\Templating::class )
			->disableOriginalConstructor()
			->getMock();
		$workflowMapper = $this->getMockBuilder( \Flow\Data\Mapper\CachingObjectMapper::class )
			->disableOriginalConstructor()
			->getMock();
		$urlGenerator = new UrlGenerator( $workflowMapper );
		$templating->method( 'getUrlGenerator' )
			->willReturn( $urlGenerator );

		$usernames = $this->getMockBuilder( \Flow\Repository\UserNameBatch::class )
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
