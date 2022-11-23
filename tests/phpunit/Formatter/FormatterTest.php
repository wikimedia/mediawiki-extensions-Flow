<?php

namespace Flow\Tests\Formatter;

use ExtensionRegistry;
use Flow\Container;
use Flow\Data\Mapper\CachingObjectMapper;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\UUID;
use Flow\Repository\UserNameBatch;
use Flow\RevisionActionPermissions;
use Flow\Templating;
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
			->willReturnCallback( 'wfMessage' );

		// Code uses wfWarn as a louder wfDebugLog in error conditions.
		// but phpunit considers a warning a fail.
		AtEase::suppressWarnings();
		$links = $this->createFormatter( \Flow\Formatter\CheckUserFormatter::class )->format( $row, $ctx );
		AtEase::restoreWarnings();
		$test( $this, $message, $links );
	}

	private function mockWorkflow( UUID $workflowId, Title $title ) {
		$workflow = $this->createMock( \Flow\Model\Workflow::class );
		$workflow->method( 'getId' )
			->willReturn( $workflowId );
		$workflow->method( 'getArticleTitle' )
			->willReturn( $title );
		return $workflow;
	}

	private function mockRevision( $changeType, UUID $revId, UUID $postId = null ) {
		$revision = $this->createMock( $postId ? \Flow\Model\PostRevision::class : \Flow\Model\Header::class );
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

	private function createFormatter( $class ) {
		$permissions = $this->createMock( RevisionActionPermissions::class );
		$permissions->method( 'isAllowed' )
			->willReturn( true );
		$permissions->method( 'getActions' )
			->willReturn( Container::get( 'flow_actions' ) );

		$templating = $this->createMock( Templating::class );
		global $wgFlowMaxThreadingDepth;
		$serializer = new RevisionFormatter(
			$permissions,
			$templating,
			new UrlGenerator( $this->createMock( CachingObjectMapper::class ) ),
			$this->createMock( UserNameBatch::class ),
			$wgFlowMaxThreadingDepth
		);

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
