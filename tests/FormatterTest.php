<?php

namespace Flow\Tests;

use Flow\Model\UUID;

/**
 * @group Flow
 */
class FormatterTest extends \MediaWikiTestCase {

	static public function checkUserProvider() {
		$postId = UUID::create();
		$topicId = UUID::create();

		$expectNull = function( $test, $message, $links ) {
			$test->assertNull( $links, $message );
		};

		return array(
			array(
				'With only a topicId reply should not fail',
				// result must contain
				function( $test, $message, $links ) {
					$test->assertArrayHasKey( 'topic', $links, $message );
					$test->assertArrayNotHasKey( 'post', $links, $message );
				},
				// cuc_comment parameters
				'reply', $topicId, null
			),

			array(
				'With topicId and postId should not fail',
				function( $test, $message, $links ) {
					$test->assertArrayHasKey( 'topic', $links, $message );
					$test->assertArrayHasKey( 'post', $links, $message );
				},
				'reply', $topicId, $postId
			),

			array(
				'an unknown action will fail gracefully',
				$expectNull,
				'fiddle', $topicId, $postId,
			),
		);
	}

	/**
	 * @dataProvider checkUserProvider
	 */
	public function testCheckUserFormatter( $message, $test, $action, UUID $workflowId, UUID $postId = null ) {
		if ( !class_exists( 'CheckUser' ) ) {
			$this->markTestSkipped( 'CheckUser is not available' );
			return;
		}

		// @fixme code smell, duplicating code from elsewhere in test
		$comment = $action . ',' . $workflowId->getHex();
		if ( $postId ) {
			$comment .= ',' . $postId->getHex();
		}

		$row = (object) array(
			'cuc_type' => RC_FLOW,
			'cuc_comment' => $comment,
			'cuc_namespace' => NS_USER_TALK,
			'cuc_title' => 'Test',
		);

		$checkUser = $this->attachContext( $this->getMock( 'CheckUser' ) );
		$checkUser->expects( $this->any() )
			->method( 'msg' )
			->will( $this->returnCallback( function() {
				return call_user_func_array( 'wfMessage', func_get_args() );
			} ) );

		// Code uses wfWarn as a louder wfDebugLog in error conditions.
		// but phpunit considers a warning a fail.
		wfSuppressWarnings();
		$links = $this->createFormatter( 'Flow\CheckUser\Formatter' )->format( $checkUser, $row );
		wfRestoreWarnings();
		$test( $this, $message, $links );
	}

	protected function attachContext( $mock ) {
		global $wgLang;
		$ctx = $this->getMock( 'IContextSource' );
		$ctx->expects( $this->any() )
			->method( 'getLanguage' )
			->will( $this->returnValue( $wgLang ) );
		$mock->expects( $this->any() )
			->method( 'getContext' )
			->will( $this->returnValue( $ctx ) );

		return $mock;
	}

	protected function createFormatter( $class ) {
		// @todo this seems like alot to mock to make a formatter ...
		// should look into how to limit the dependencies
		$storage = $this->getMockBuilder( 'Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();
		$actions = $this->getMockBuilder( 'Flow\FlowActions' )
			->disableOriginalConstructor()
			->getMock();
		$templating = $this->getMockBuilder( 'Flow\Templating' )
			->disableOriginalConstructor()
			->getMock();
		$urlGenerator = $this->getMockBuilder( 'Flow\UrlGenerator' )
			->disableOriginalConstructor()
			->getMock();
		$templating->expects( $this->any() )
			->method( 'getUrlGenerator' )
			->will( $this->returnValue( $urlGenerator ) );

		return new $class( $storage, $actions, $templating );
	}
}
