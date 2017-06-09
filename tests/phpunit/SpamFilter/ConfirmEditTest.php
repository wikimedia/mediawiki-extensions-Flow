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
		$title = Title::newFromText( 'Topic:Tnprd6ksfu1v1nme' );
		$ownerTitle = Title::newMainPage();
		$workflow = Workflow::create( 'topic', $title );

		$oldRevision = PostRevision::createTopicPost( $workflow, $user, 'foo' );
		$newRevision = $oldRevision->newNextRevision( $user, 'bar', 'topic-title-wikitext', 'edit-title', $title );

		$request = $this->getMock( 'WebRequest' );
		$request->expects( $this->any() )
			->method( 'wasPosted' )
			->will( $this->returnValue( true ) );

		$context = $this->getMock( 'IContextSource' );

		$context->expects( $this->any() )
			->method( 'getUser' )
			->will( $this->returnValue( $user ) );

		$context->expects( $this->any() )
			->method( 'getRequest' )
			->will( $this->returnValue( $request ) );

		$status = $filter->validate( $context, $newRevision, $oldRevision, $title, $ownerTitle );
		$this->assertInstanceOf( 'Status', $status );
		$this->assertTrue( $status->isGood() );
	}
}
