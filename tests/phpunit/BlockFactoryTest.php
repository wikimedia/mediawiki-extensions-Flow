<?php

namespace Flow\Tests;

use Flow\BlockFactory;

/**
 * @group Flow
 */
class BlockFactoryTest extends FlowTestCase {

	public function provideDataCreateBlocks() {
		return [
			[ 'discussion', [ 'Flow\Block\HeaderBlock', 'Flow\Block\TopicListBlock', 'Flow\Block\BoardHistoryBlock' ] ],
			[ 'topic', [ 'Flow\Block\TopicBlock', 'Flow\Block\TopicSummaryBlock' ] ],
		];
	}

	/**
	 * @dataProvider provideDataCreateBlocks
	 */
	public function testCreateBlocks( $workflowType, $expectedResults ) {
		$factory = $this->createBlockFactory();
		$workflow = $this->mockWorkflow( $workflowType );

		$blocks = $factory->createBlocks( $workflow );
		$this->assertEquals( count( $blocks ), count( $expectedResults ) );

		$results = [];
		foreach ( $blocks as $obj ) {
			$results[] = get_class( $obj );
		}
		$this->assertEquals( $results, $expectedResults );
	}

	/**
	 * @expectedException \Flow\Exception\DataModelException
	 */
	public function testCreateBlocksWithInvalidInputException() {
		$factory = $this->createBlockFactory();
		$workflow = $this->mockWorkflow( 'a-bad-database-flow-workflow' );
		// Trigger DataModelException
		$factory->createBlocks( $workflow );
	}

	protected function createBlockFactory() {
		$storage = $this->getMockBuilder( '\Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		$rootPostLoader = $this->getMockBuilder( '\Flow\Repository\RootPostLoader' )
			->disableOriginalConstructor()
			->getMock();

		return new BlockFactory( $storage, $rootPostLoader );
	}

	protected function mockWorkflow( $type ) {
		$workflow = $this->getMockBuilder( '\Flow\Model\Workflow' )
			->disableOriginalConstructor()
			->getMock();
		$workflow->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		return $workflow;
	}
}
