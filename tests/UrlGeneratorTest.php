<?php

namespace Flow\Tests;

use Flow\Container;
use \Title;

/**
 * @group Flow
 */
class UrlGeneratorTest extends FlowTestCase {

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
		$urlGenerator = Container::get( 'url_generator' );
		$anchor = $urlGenerator->BoardLink( $title, $sortBy, $saveSortBy );
		$this->assertInstanceOf( '\Flow\Anchor', $anchor );

		$link = $anchor->getFullURL();
		$option = parse_url( $link );
		$this->assertArrayHasKey( 'query', $option );
		parse_str( $option['query'], $query );

		if ( $sortBy !== null ) {
			$this->assertEquals( $sortBy, $query['topiclist_sortby'] );
			if ( $saveSortBy ) {
				$this->assertEquals( '', $query['topiclist_savesortby'] );
			}
		}
	}

}
