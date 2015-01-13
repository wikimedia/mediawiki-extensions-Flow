<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Model\UUID;
use Title;

/**
 * @group Flow
 */
class UrlGeneratorTest extends FlowTestCase {

	protected $urlGenerator;

	protected function setUp() {
		parent::setUp();
		$this->urlGenerator = Container::get( 'url_generator' );
	}

	public function provideDataBoardLink() {
		return array (
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				'updated',
				true
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				'updated',
				false
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				'created',
				true
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				'created',
				false
			)
		);
	}

	/**
	 * @dataProvider provideDataBoardLink
	 */
	public function testBoardLink( Title $title, $sortBy = null, $saveSortBy = false ) {
		$anchor = $this->urlGenerator->boardLink( $title, $sortBy, $saveSortBy );
		$this->assertInstanceOf( '\Flow\Model\Anchor', $anchor );

		$link = $anchor->getFullURL();
		$option = parse_url( $link );
		$this->assertArrayHasKey( 'query', $option );
		parse_str( $option['query'], $query );

		if ( $sortBy !== null ) {
			$this->assertEquals( $sortBy, $query['topiclist_sortby'] );
			if ( $saveSortBy ) {
				$this->assertEquals( '1', $query['topiclist_savesortby'] );
			}
		}
	}

	public function provideDataWatchTopicLink() {
		return array (
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				UUID::create()
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				UUID::create()
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				UUID::create()
			),
			array(
				Title::makeTitle( NS_MAIN, 'Test' ),
				UUID::create()
			)
		);
	}

	/**
	 * @dataProvider provideDataWatchTopicLink
	 */
	public function testWatchTopicLink( Title $title, $workflowId ) {
		$anchor = $this->urlGenerator->watchTopicLink( $title, $workflowId );
		$this->assertInstanceOf( '\Flow\Model\Anchor', $anchor );

		$link = $anchor->getFullURL();
		$option = parse_url( $link );
		$this->assertArrayHasKey( 'query', $option );
		parse_str( $option['query'], $query );
		$this->assertEquals( 'watch', $query['action'] );
	}

	/**
	 * @dataProvider provideDataWatchTopicLink
	 */
	public function testUnwatchTopicLink( Title $title, $workflowId ) {
		$anchor = $this->urlGenerator->unwatchTopicLink( $title, $workflowId );
		$this->assertInstanceOf( '\Flow\Model\Anchor', $anchor );

		$link = $anchor->getFullURL();
		$option = parse_url( $link );
		$this->assertArrayHasKey( 'query', $option );
		parse_str( $option['query'], $query );
		$this->assertEquals( 'unwatch', $query['action'] );
	}
}
