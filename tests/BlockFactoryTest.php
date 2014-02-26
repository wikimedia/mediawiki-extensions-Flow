<?php

namespace Flow\Tests;

use Flow\BlockFactory;
use Flow\NotificationController;

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
	public function testCreateBlocks( $workflowType, $expectedResults ) {
		$factory = $this->createBlockFactory();
		$workflow = $this->mockWorkflow( $workflowType );

		$blocks = $factory->createBlocks( $workflow );
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
		$workflow = $this->mockWorkflow( 'a-bad-database-flow-workflow' );
		// Trigger InvalidInputException
		$factory->createBlocks( $workflow );
	}

	protected function createBlockFactory() {
		global $wgLang;

		$storage = $this->getMockBuilder( '\Flow\Data\ManagerGroup' )
			->disableOriginalConstructor()
			->getMock();

		// phpunit mocker fails to generate the correct method definition for
		// NotificationController::getDefaultNotifiedUsers, just use the real one
		$notificationController = new NotificationController( $wgLang );

		$rootPostLoader = $this->getMockBuilder( '\Flow\Data\RootPostLoader' )
			->disableOriginalConstructor()
			->getMock();

		return new BlockFactory( $storage, $notificationController, $rootPostLoader );
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
