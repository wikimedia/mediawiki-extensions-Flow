<?php

namespace Flow\Tests;

use Flow\Model\UserTuple;
use Flow\Model\XmlTopic;
use Flow\ParsoidUtils;
use DOMText;
use User;

class XmlTopicTest extends \MediaWikiTestCase {

	public function testCreateNewTopicWithOnlyTitle() {
		$topic = $this->newTopic();
		$status = $topic->editTitle( new User, 'foobar' );
		$this->assertTrue( $status->isGood() );
		$newTopic = $topic->createNextRevision();
	}

	public function testCreateNewTopicWithReply() {
		$topic = $this->newTopic();
		// replies and other content besides the plain text titles expect
		// DOMElement
		$status = $topic->reply( new User, $this->createParagraph( 'foobar' ) );
		$this->assertTrue( $status->isGood() );
		$newTopic = $topic->createNextRevision();
	}

	public function testCreateNestedReply() {
		$topic = $this->newTopic();
		$status = $topic->reply( new User, $this->createParagraph( 'foobar' ) );
		$this->assertTrue( $status->isGood() );
		$command = $status->getValue();
		$newTopic = $topic->createNextRevision();
		$status = $newTopic->reply(
			new User,
			$this->createParagraph( 'foo' ),
			$command->getPost()->getPostId()
		);
		$this->assertTrue( $status->isGood() );
		$newNewTopic = $newTopic->createNextRevision();
	}

	public function testEditReply() {
		$topic = $this->newTopic();
		$status = $topic->reply( new User, $this->createParagraph( 'foobar' ) );
		$this->assertTrue( $status->isGood() );
		$command = $status->getValue();
		$newTopic = $topic->createNextRevision();
		$status = $newTopic->edit(
			new User,
			$this->createParagraph( 'edited' ),
			$command->getPost()->getRevisionId()
		);
		$this->assertTrue( $status->isGood() );
		$newNewTopic = $newTopic->createNextRevision();
		// $this->dump( $newNewTopic );
	}

	protected function dump( $revisionable ) {
		var_dump( html_entity_decode( $revisionable->getHtml() ) );
	}

	protected function newTopic() {
		$permissions = $this->getMockBuilder( 'Flow\RevisionActionPermissions' )
			->disableOriginalConstructor()
			->getMock();
		$permissions->expects( $this->any() )
			->method( 'isAllowed' )
			->will( $this->returnValue( 'true' ) );

		return new XmlTopic( $permissions );
	}

	protected function createParagraph( $content ) {
		return ParsoidUtils::createDom( $content )
			->getElementsByTagName( 'p' )
			->item( 0 );
	}
}
