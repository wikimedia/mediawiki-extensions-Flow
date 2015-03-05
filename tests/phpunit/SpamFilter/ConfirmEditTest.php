<?php

namespace Flow\Tests\SpamFilter;


use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\SpamFilter\ConfirmEdit;
use Title;
use User;

class ConfirmEditTest extends \MediaWikiTestCase {

	public function testValidateDoesntBlowUp() {
		$filter = new ConfirmEdit;
		if ( !$filter->enabled() ) {
			$this->markTestSkipped( 'ConfirmEdit is not enabled' );
		}

		$user = User::newFromName( '127.0.0.1', false );
		$title = Title::newMainPage();
		$workflow = Workflow::create( 'topic', $title );

		$oldRevision = PostRevision::create( $workflow, $user, 'foo', 'wikitext' );
		$newRevision = $oldRevision->newNextRevision( $user, 'bar', 'wikitext', 'change-type', $title );

		$status = $filter->validate( $newRevision, $oldRevision, $title );
		$this->assertInstanceOf( 'Status', $status );
		$this->assertTrue( $status->isGood() );
	}
}
