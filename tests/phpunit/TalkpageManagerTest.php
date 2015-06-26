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
	public function testIsTalkpageOccupied( array $pages, array $ns, Title $title, $checkContentModel, $expected ) {
		$manager = new TalkpageManager( $ns, $pages );
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
				array( 'Talk:Foo Bar' ),
				array(),
				Title::newFromText( 'Talk:Foo Bar' ),
				false,
				true
			),
			array(
				array(),
				array( NS_USER_TALK ),
				Title::newFromText( 'User talk:Foo Bar' ),
				false,
				true
			),
			/*
			  Temporarily disabled for T103776

			array(
				array(),
				array( NS_USER_TALK ),
				Title::newFromText( 'User talk:Foo/Bar' ),
				false,
				true
			),
			*/
			array(
				array(),
				array( NS_USER_TALK ),
				Title::newFromText( 'Talk:Foo Bar' ),
				false,
				false
			),
			array(
				array(),
				array( NS_USER_TALK ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				true
			),
			array(
				array(),
				array( NS_USER_TALK ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				true
			),
			array(
				array(),
				array( NS_USER_TALK ),
				$this->getMockTitle( true, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array(),
				array( NS_USER_TALK ),
				$this->getMockTitle( true, 'wikitext', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				false
			),
			array(
				array(),
				array( NS_TALK ),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array(),
				array(),
				$this->getMockTitle( false, 'wikitext', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				false
			),
			array(
				array(),
				array(),
				$this->getMockTitle( true, 'flow-board', NS_USER_TALK, 'User talk:FooBar' ),
				true,
				true
			),
			array(
				array(),
				array(),
				$this->getMockTitle( true, 'flow-board', NS_USER_TALK, 'User talk:Foo/Bar' ),
				true,
				true
			),
		);
	}
}
