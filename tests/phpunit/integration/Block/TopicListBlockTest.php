<?php

namespace Flow\Tests\Block;

use Flow\Block\TopicListBlock;
use Flow\Container;
use Flow\Hooks;
use Flow\Model\Workflow;
use MediaWiki\MediaWikiServices;
use Title;
use User;

/**
 * @covers \Flow\Block\TopicListBlock
 */
class TopicListBlockTest extends \MediaWikiIntegrationTestCase {

	public function testSortByOption() {
		$user = User::newFromId( 1 );
		$this->getServiceContainer()->getUserOptionsManager()
			->setOption( $user, 'flow-topiclist-sortby', '' );

		// reset flow state, so everything ($container['permissions'])
		// uses this particular $user
		Hooks::resetFlowExtension();
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user;

		$ctx = $this->createMock( \IContextSource::class );
		$ctx->method( 'getUser' )
			->willReturn( $user );

		$workflow = Workflow::create( 'discussion', Title::newFromText( 'Talk:Flow_QA' ) );
		$block = new TopicListBlock( $workflow, Container::get( 'storage' ) );
		$block->init( $ctx, 'view' );

		$res = $block->renderApi( [
		] );
		$this->assertEquals( 'newest', $res['sortby'], 'With no sortby defaults to newest' );

		$res = $block->renderApi( [
			'sortby' => 'foo',
		] );
		$this->assertEquals( 'newest', $res['sortby'], 'With invalid sortby defaults to newest' );

		$res = $block->renderApi( [
			'sortby' => 'updated',
		] );
		$this->assertEquals( 'updated', $res['sortby'], 'With sortby updated output changes to updated' );
		$res = $block->renderApi( [
		] );
		$this->assertEquals( 'newest', $res['sortby'], 'Sort still defaults to newest' );

		$res = $block->renderApi( [
			'sortby' => 'updated',
			'savesortby' => '1',
		] );
		$this->assertEquals( 'updated', $res['sortby'], 'Request saving sortby option' );

		// The preference is saved via a job; run the job for the next set of assertions.
		MediaWikiServices::getInstance()->getJobRunner()->run( [
			'type' => 'userOptionsUpdate'
		] );

		$res = $block->renderApi( [
		] );
		$this->assertEquals( 'updated', $res['sortby'], 'Default sortby now changed to updated' );

		$res = $block->renderApi( [
			'sortby' => '',
		] );
		$this->assertEquals( 'updated', $res['sortby'], 'Default sortby with blank sortby still uses user default' );
	}
}
