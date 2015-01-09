<?php

namespace Flow\Tests\Block;

use Flow\Block\TopicListBlock;
use Flow\Container;
use Flow\Model\Workflow;
use Title;
use User;

class TopicListTest extends \MediaWikiTestCase {

	public function testSortByOption() {
		$user = User::newFromId( 1 );
		$user->setOption( 'flow-topiclist-sortby', '' );

		$ctx = $this->getMock( 'IContextSource' );
		$ctx->expects( $this->any() )
			->method( 'getUser' )
			->will( $this->returnValue( $user ) );

		$workflow = Workflow::create( 'discussion', Title::newFromText( 'Talk:Flow_QA' ) );
		$block = new TopicListBlock( $workflow, Container::get( 'storage' ) );
		$block->init( $ctx, 'view' );

		$res = $block->renderApi( array(
		) );
		$this->assertEquals( 'newest', $res['sortby'], 'With no sortby defaults to newest' );

		$res = $block->renderApi( array(
			'sortby' => 'foo',
		) );
		$this->assertEquals( 'newest', $res['sortby'], 'With invalid sortby defaults to newest' );

		$res = $block->renderApi( array(
			'sortby' => 'updated',
		) );
		$this->assertEquals( 'updated', $res['sortby'], 'With sortby updated output changes to updated' );
		$res = $block->renderApi( array(
		) );
		$this->assertEquals( 'newest', $res['sortby'], 'Sort still defaults to newest' );

		$res = $block->renderApi( array(
			'sortby' => 'updated',
			'savesortby' => '1',
		) );
		$this->assertEquals( 'updated', $res['sortby'], 'Request saving sortby option' );

		$res = $block->renderApi( array(
		) );
		$this->assertEquals( 'updated', $res['sortby'], 'Default sortby now changed to updated' );

		$res = $block->renderApi( array(
			'sortby' => '',
		) );
		$this->assertEquals( 'updated', $res['sortby'], 'Default sortby with blank sortby still uses user default' );
	}
}
