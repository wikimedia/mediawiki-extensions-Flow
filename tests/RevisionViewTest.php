<?php

namespace Flow\Tests;

use Flow\View\RevisionView;
use Flow\View\HeaderRevisionView;
use Flow\View\PostRevisionView;
use Flow\Model\UUID;
use ReflectionClass;

class RevisionViewTest extends \MediaWikiTestCase {

	public function testNewFromId() {
		$id = UUID::create( md5( time() ) );

		// Header revision
		$view = HeaderRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockHeaderBlock( true ) );
		$this->assertInstanceOf( '\Flow\View\HeaderRevisionView', $view );
		$view = HeaderRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockHeaderBlock( false ) );
		$this->assertFalse( $view );

		// Post revision
		$view = PostRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockTopicBlock( true ) );
		$this->assertInstanceOf( '\Flow\View\PostRevisionView', $view );
		$view = PostRevisionView::newFromId( $id, $this->mockTemplating(), $this->mockTopicBlock( false ) );
		$this->assertFalse( $view );
	}

	public function testRenderDiffViewAgainstNullRevision() {
		// Header revision
		$view = HeaderRevisionView::newFromId( UUID::create( md5( time() ) ), $this->mockTemplating(), $this->mockHeaderBlock( true ) );

		$reflection = new ReflectionClass( $view );
		$property = $reflection->getProperty( 'block' );
		$property->setAccessible( true );
		$property->setValue( $view, $this->mockHeaderBlock( false ) );

		$this->setExpectedException( '\Flow\Exception\InvalidInputException', 'Attempt to compare against null revision' );
		$view->renderDiffViewAgainst( UUID::create( md5( time() + 1000 ) ) );
	}

	public function testRenderDiffViewAgainstInvalidRevision() {
		// Header revision
		$view = HeaderRevisionView::newFromId( UUID::create( md5( time() ) ), $this->mockTemplating(), $this->mockHeaderBlock( true ) );

		$reflection = new ReflectionClass( $view );
		$property = $reflection->getProperty( 'block' );
		$property->setAccessible( true );
		$property->setValue( $view, $this->mockTopicBlock( true ) );

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
		return $revision;
	}

	protected function mockTemplating() {
		$templating = $this->getMockBuilder( '\Flow\Templating' )
			->disableOriginalConstructor()
			->getMock();
		return $templating;
	}

}
