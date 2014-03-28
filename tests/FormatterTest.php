<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\UUID;
use Flow\UrlGenerator;

/**
 * @group Flow
 */
class FormatterTest extends \MediaWikiTestCase {

	static public function checkUserProvider() {
		$topicId = UUID::create();
		$revId = UUID::create();
		$postId = UUID::create();

		$expectNull = function( $test, $message, $links ) {
			$test->assertNull( $links, $message );
		};

		return array(
			array(
				'With only a topicId reply should not fail',
				// result must contain
				function( $test, $message, $links ) {
					$test->assertNotNull( $links );
					$test->assertArrayHasKey( 'topic', $links, $message );
					$test->assertArrayNotHasKey( 'post', $links, $message );
				},
				// cuc_comment parameters
				'reply', $topicId, $revId, null
			),

			array(
				'With topicId and postId should not fail',
				function( $test, $message, $links ) {
					$test->assertNotNull( $links );
					$test->assertArrayHasKey( 'topic', $links, $message );
					$test->assertArrayHasKey( 'post', $links, $message );
				},
				'reply', $topicId, $postId, $revId,
			),

			array(
				'an unknown action will fail gracefully',
				$expectNull,
				'fiddle', $topicId, $postId, $revId,
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

		// @fixme code smell, duplicating code from elsewhere in test
		$comment = implode( ',', array(
			'v1', // serialization version
			$action,
			$workflowId->getAlphadecimal(),
			$revId->getAlphadecimal(),
		) );
		if ( $postId ) {
			$comment .= ',' . $postId->getAlphadecimal();
		}

		$row = (object) array(
			'cuc_type' => RC_FLOW,
			'cuc_comment' => $comment,
			'cuc_namespace' => NS_USER_TALK,
			'cuc_title' => 'Test',
		);

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
		$links = $this->createFormatter( 'Flow\Formatter\CheckUser' )->format( $row, $ctx );
		wfRestoreWarnings();
		$test( $this, $message, $links );
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
		$workflowStorage = $this->getMockBuilder( 'Flow\Data\ObjectManager' )
			->disableOriginalConstructor()
			->getMock();
		$occupier = $this->getMockBuilder( 'Flow\OccupationController' )
			->disableOriginalConstructor()
			->getMock();
		$urlGenerator = new UrlGenerator( $workflowStorage, $occupier );
		$templating->expects( $this->any() )
			->method( 'getUrlGenerator' )
			->will( $this->returnValue( $urlGenerator ) );

		return new $class( $permissions, $templating );
	}
}
