<?php

namespace Flow\Tests;

use Flow\BlockFactory;
use Flow\Container;
use ReflectionClass;

/**
 * @group Flow
 */
class BlockFactoryTest extends FlowTestCase {

	public function provideDataCreateBlocks() {
		return array (
			array( 'discussion', array( 'Flow\Block\HeaderBlock', 'Flow\Block\TopicListBlock', 'Flow\Block\BoardHistoryBlock' ) ),
			array( 'topic', array( 'Flow\Block\TopicBlock', 'Flow\Block\TopicSummaryBlock' ) ),
		);
	}

	/**
	 * @dataProvider provideDataCreateBlocks
	 */
	public function testCreateBlocks( $definitionType, $expectedResults ) {
		$factory = $this->createBlockFactory();
		list( $definition, $workflow ) = $this->mockWorkflow( $definitionType );

		$blocks = $factory->createBlocks( $definition, $workflow );
		$this->assertEquals( count( $blocks ), count( $expectedResults ) );

		$results = array();
		foreach ( $blocks as $obj ) {
			$results[] = get_class( $obj );
		}
		$this->assertEquals( $results, $expectedResults );
	}

	/**
	 * @expectedException \Flow\Exception\InvalidInputException
	 */
	public function testCreateBlocksWithInvalidInputException() {
		$factory = $this->createBlockFactory();
		list( $definition, $workflow ) = $this->mockWorkflow( 'a-bad-database-flow-definition' );
		// Trigger InvalidInputException
		$factory->createBlocks( $definition, $workflow );
	}

	protected function createBlockFactory() {
		$storage = $this->getMockBuilder( '\Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		$notificationController = $this->getMockBuilder( '\Flow\NotificationController' )
			->disableOriginalConstructor()
			->getMock();

		$rootPostLoader = $this->getMockBuilder( '\Flow\Data\RootPostLoader' )
			->disableOriginalConstructor()
			->getMock();

		return new BlockFactory( $storage, $notificationController, $rootPostLoader );
	}

	protected function mockWorkflow( $type ) {
		$definition = $this->getMockBuilder( '\Flow\Model\Definition' )
			->disableOriginalConstructor()
			->getMock();
		$definition->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		$workflow = $this->getMockBuilder( '\Flow\Model\Workflow' )
			->disableOriginalConstructor()
			->getMock();

		return array( $definition, $workflow );
	}
}
