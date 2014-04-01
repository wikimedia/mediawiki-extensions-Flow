<?php

namespace Flow\Tests;

use Flow\Model\UUID;
use Flow\View\HeaderRevisionView;
use Flow\View\PostRevisionView;
use ReflectionClass;

class RevisionViewTest extends \MediaWikiTestCase {

	protected $user;

	public function setUp() {
		global $wgUser;

		parent::setUp();
		$this->user = $wgUser;
	}

	public function testNewFromId() {
		$id = UUID::create( md5( time() ) );

		// Header revision with valid record from storage
		$view = HeaderRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockHeaderBlock( true ), $this->user );
		$this->assertInstanceOf( '\Flow\View\HeaderRevisionView', $view );
		// Header revision with invalid record from storage
		$view = HeaderRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockHeaderBlock( false ), $this->user );
		$this->assertFalse( $view );

		// Post revision with valid record from storage
		$view = PostRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockTopicBlock( true ), $this->user );
		$this->assertInstanceOf( '\Flow\View\PostRevisionView', $view );
		// Post revision with invalid record from storage
		$view = PostRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockTopicBlock( false ), $this->user );
		$this->assertFalse( $view );
	}

	public function testRenderDiffViewAgainstNullRevision() {
		// Header revision
		$view = HeaderRevisionView::newFromId( UUID::create( md5( time() ) ), $this->mockTemplating(), $this->mockHeaderBlock( true ), $this->user );

		// Make the storage return null on rev_id lookup
		$reflection = new ReflectionClass( $view );
		$property = $reflection->getProperty( 'block' );
		$property->setAccessible( true );
		$property->setValue( $view, $this->mockHeaderBlock( false ) );

		// Exception since it compares against a null revision
		$this->setExpectedException( '\Flow\Exception\InvalidInputException', 'Attempt to compare against null revision' );
		$view->renderDiffViewAgainst( UUID::create( md5( time() + 1000 ) ) );
	}

	public function testRenderDiffViewAgainstInvalidRevision() {
		// Header revision
		$view = HeaderRevisionView::newFromId( UUID::create( md5( time() ) ), $this->mockTemplating(), $this->mockHeaderBlock( true ), $this->user );

		// Make the storage return a Post revision on rev_id lookup
		$reflection = new ReflectionClass( $view );
		$property = $reflection->getProperty( 'block' );
		$property->setAccessible( true );
		$property->setValue( $view, $this->mockTopicBlock( true ) );

		// Exception since it compares against a revision of different type
		$this->setExpectedException( '\Flow\Exception\InvalidInputException', 'Attempt to compare revisions of different types' );
		$view->renderDiffViewAgainst( UUID::create( md5( time() + 1000 ) ) );
	}

	protected function mockHeaderBlock( $status ) {
		$block = $this->getMockBuilder( '\Flow\Block\HeaderBlock' )
			->disableOriginalConstructor()
			->getMock();
		$block->expects( $this->any() )
			->method( 'getStorage' )
			->will( $this->returnValue( $this->mockStorage( 'Header', $status ) ) );
		return $block;
	}

	protected function mockTopicBlock( $status ) {
		$block = $this->getMockBuilder( '\Flow\Block\TopicBlock' )
			->disableOriginalConstructor()
			->getMock();
		$block->expects( $this->any() )
			->method( 'getStorage' )
			->will( $this->returnValue( $this->mockStorage( 'PostRevision', $status ) ) );
		return $block;
	}

	protected function mockStorage( $revType, $status ) {
		$storage = $this->getMockBuilder( '\Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();
		$storage->expects( $this->any() )
			->method( 'get' )
			->will( $this->returnValue( $status? $this->mockRevision( $revType ): false ) );
		return $storage;
	}

	protected function mockRevision( $revType ) {
		$revision = $this->getMockBuilder( '\Flow\Model\\' . $revType )
			->disableOriginalConstructor()
			->getMock();
		$revision->expects( $this->any() )
			->method( 'getCollectionId' )
			->will( $this->returnValue( UUID::create( md5( 'same collection id' ) ) ) );
		$revision->expects( $this->any() )
			->method( 'getRevisionType' )
			->will( $this->returnValue( $revType ) );
		if ( $revType === 'PostRevision' ) {
			$revision->expects( $this->any() )
				->method( 'getPostId' )
				->will( $this->returnValue( UUID::create( md5( time() + 1000 ) ) ) );
		}
		return $revision;
	}

	protected function mockTemplating() {
		$templating = $this->getMockBuilder( '\Flow\Templating' )
			->disableOriginalConstructor()
			->getMock();
		return $templating;
	}

}
