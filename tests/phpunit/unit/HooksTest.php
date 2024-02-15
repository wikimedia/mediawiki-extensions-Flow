<?php

namespace Flow\Tests\Unit;

use Flow\Data\Listener\RecentChangesListener;
use Flow\Formatter\CheckUserQuery;
use Flow\Hooks;
use MediaWiki\User\UserIdentityValue;
use MediaWikiUnitTestCase;
use RecentChange;

/**
 * @covers \Flow\Hooks
 */
class HooksTest extends MediaWikiUnitTestCase {

	/** @dataProvider provideOnCheckUserInsertChangesRow */
	public function testOnCheckUserInsertChangesRow( &$row, $rcAttribs, $expectedRowAfterCall ) {
		// The unit test is needed so that we can test the hook handler even if CheckUser is not
		// installed. An integration test verifies that the hook handler is called when CheckUser
		// runs this hook.
		//
		// IP and XFF have to be passed by reference, so have to be variables in this test method.
		$ip = 'unused';
		$xff = 'unused';
		$user = UserIdentityValue::newAnonymous( '127.0.0.1' );
		if ( count( $rcAttribs ) ) {
			$rc = new RecentChange;
			$rc->setAttribs( $rcAttribs );
		} else {
			$rc = null;
		}
		// Call the hook handler (which is under test)
		Hooks::onCheckUserInsertChangesRow( $ip, $xff, $row, $user, $rc );
		$this->assertArrayEquals(
			$expectedRowAfterCall, $row, true, true,
			'The $row argument passed by reference was not as expected after the hook handler was called.'
		);
	}

	public static function provideOnCheckUserInsertChangesRow() {
		return [
			'RecentChange object provided that is not for a Flow change' => [
				// The $row provided by reference to the hook handler
				[],
				// The attributes for the RecentChange object provided to the hook handler
				[ 'rc_source' => RecentChange::SRC_EDIT ],
				// The expected value of $row after the hook handler is called
				[],
			],
			'No RecentChange object provided' => [ [], [], [] ],
			'RecentChange object provided with Flow as the source' => [
				[],
				[
					'rc_source' => RecentChangesListener::SRC_FLOW,
					'rc_params' => serialize( [ 'flow-workflow-change' => [
						'action' => 'action',
						'workflow' => 'workflow',
						'revision' => 'revision',
					] ] ),
				],
				[ 'cuc_comment' => CheckUserQuery::VERSION_PREFIX . ',action,workflow,revision' ]
			]
		];
	}
}
