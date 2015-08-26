<?php

namespace Flow\Tests;

use Flow\TalkpageManager;
use MediaWikiLangTestCase;
use Title;

class TalkpageManagerTest extends MediaWikiLangTestCase {

	/**
	 * @covers TalkpageManager::isTalkpageOccupied
	 * @dataProvider provideIsTalkpageOccupied
	 */
	public function testIsTalkpageOccupied( array $ns, Title $title, $checkContentModel, $expected ) {
		$this->setMwGlobals( 'wgNamespaceContentModels', $ns );
		$manager = new TalkpageManager();
		$actual = $manager->isTalkpageOccupied( $title, $checkContentModel );
		$this->assertEquals( $expected, $actual );
	}

	private function getMockTitle( $exists, $model, $ns, $text ) {
		/** @var \PHPUnit_Framework_MockObject_MockObject $mock */
		$mock = $this->getMockBuilder( 'Title' )
			->setMethods( array( 'exists', 'getContentModel', 'getNamespace', 'getPrefixedText' ) )
			->disableOriginalConstructor()
			->getMock();
		$mock->expects( $this->any() )->method( 'exists' )->will( $this->returnValue( $exists ) );
		$mock->expects( $this->any() )->method( 'getContentModel' )->will( $this->returnValue( $model ) );
		$mock->expects( $this->any() )->method( 'getNamespace' )->will( $this->returnValue( $ns ) );
		$mock->expects( $this->any() )->method( 'getPrefixedText' )->will( $this->returnValue( $text ) );

		return $mock;
	}

	public function provideIsTalkpageOccupied() {
		return array(
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				Title::newFromText( 'User talk:Foo Bar' ),
				false,
				true
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				Title::newFromText( 'User talk:Foo/Bar' ),
				false,
				true
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				Title::newFromText( 'Talk:Foo Bar' ),
				false,
				false
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				true
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				true
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				$this->getMockTitle( true, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array( NS_USER_TALK => CONTENT_MODEL_FLOW_BOARD ),
				$this->getMockTitle( true, 'wikitext', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				false
			),
			array(
				array( NS_TALK => CONTENT_MODEL_FLOW_BOARD ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array(),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array(),
				$this->getMockTitle( true, 'flow-board', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				true
			),
			array(
				array(),
				$this->getMockTitle( true, 'flow-board', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				true
			),
		);
	}
}
