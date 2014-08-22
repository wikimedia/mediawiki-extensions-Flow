<?php

namespace Flow\Tests;

use Flow\Data\PagerPage;
use Flow\Data\Pager;
use Flow\Model\UUID;

/**
 * @group Flow
 */
class PagerTest extends FlowTestCase {

	public function provideDataMakePagingLink() {
		return array (
			array(
				$this->mockStorage(
					array(
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry()
					),
					UUID::create(),
					array( 'topic_id' )
				),
				array( 'topic_list_id' => '123456' ),
				array( 'pager-limit' => 2, 'order' => 'desc', 'sort' => 'topic_id' ),
				'offset-id'
			),
			array(
				$this->mockStorage(
					array(
						$this->mockTopicListEntry(),
						$this->mockTopicListEntry()
					),
					UUID::create(),
					array( 'workflow_last_update_timestamp' )
				),
				array( 'topic_list_id' => '123456' ),
				array( 'pager-limit' => 1, 'order' => 'desc', 'sort' => 'workflow_last_update_timestamp', 'sortby' => 'updated' ),
				'offset'
			)
		);
	}

	/**
	 * @dataProvider provideDataMakePagingLink
	 */
	public function testMakePagingLink( $storage, $query, $options, $offsetKey ) {
		$pager = new Pager( $storage, $query, $options );
		$page = $pager->getPage();
		$pagingOption = $page->getPagingLinksOptions();
		foreach ( $pagingOption as $option ) {
			$this->assertArrayHasKey( $offsetKey, $option );
			$this->assertArrayHasKey( 'offset-dir', $option );
			$this->assertArrayHasKey( 'limit', $option );
			if ( isset( $options['sortby'] ) ) {
				$this->assertArrayHasKey( 'sortby', $option );
			}
		}
	}

	/**
	 * Mock the storage
	 */
	protected function mockStorage( $return, $offset, $sort ) {
		$storage = $this->getMockBuilder( 'Flow\Data\ObjectManager' )
			->disableOriginalConstructor()
			->getMock();
		$storage->expects( $this->any() )
			->method( 'find' )
			->will( $this->returnValue( $return ) );
		$storage->expects( $this->any() )
			->method( 'serializeOffset' )
			->will( $this->returnValue( $offset ) );
		$storage->expects( $this->any() )
			->method( 'getIndexFor' )
			->will( $this->returnValue( $this->mockIndex( $sort ) ) );
		return $storage;
	}

	/**
	 * Mock TopicListEntry
	 */
	protected function mockTopicListEntry() {
		$entry = $this->getMockBuilder( 'Flow\Model\TopicListEntry' )
			->disableOriginalConstructor()
			->getMock();
		return $entry;
	}

	/**
	 * Mock TopKIndex
	 */
	protected function mockIndex( $sort ) {
		$index = $this->getMockBuilder( 'Flow\Data\TopKIndex' )
			->disableOriginalConstructor()
			->getMock();
		$index->expects( $this->any() )
			->method( 'getSort' )
			->will( $this->returnValue( $sort ) );
		return $index;
	}

}
