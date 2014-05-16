<?php

namespace Flow\Tests;

use Flow\Container;
use ReflectionClass;

/**
 * @group Flow
 */
class WorkflowLoaderTest extends FlowTestCase {

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
		$workflowLoader = $this->mockWorkflowLoader( $definitionType );
		$blocks = $workflowLoader->createBlocks();
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
		// Trigger InvalidInputException
		$workflowLoader = $this->mockWorkflowLoader( 'a-bad-database-flow-definition' );
		$workflowLoader->createBlocks();
	}

	/**
	 * Create a WorkflowLoader mock object since we don't want any query
	 * against the database
	 */
	protected function mockWorkflowLoader( $type ) {
		$definition = $this->getMockBuilder( '\Flow\Model\Definition' )
			->disableOriginalConstructor()
			->getMock();
		$definition->expects( $this->any() )
			->method( 'getType' )
			->will( $this->returnValue( $type ) );

		$workflow = $this->getMockBuilder( '\Flow\Model\Workflow' )
			->disableOriginalConstructor()
			->getMock();

		$methods = array_diff( get_class_methods( '\Flow\WorkflowLoader' ), array( 'createBlocks' ) );
		$loader = $this->getMockBuilder( '\Flow\WorkflowLoader' )
			->disableOriginalConstructor()
			->setMethods( $methods )
			->getMock();

		$reflection = new ReflectionClass( $loader );

		$property = $reflection->getProperty( 'definition' );
		$property->setAccessible( true );
		$property->setValue( $loader, $definition );

		$property = $reflection->getProperty( 'workflow' );
		$property->setAccessible( true );
		$property->setValue( $loader, $workflow );

		$property = $reflection->getProperty( 'storage' );
		$property->setAccessible( true );
		$property->setValue( $loader, Container::get( 'storage' ) );

		$property = $reflection->getProperty( 'notificationController' );
		$property->setAccessible( true );
		$property->setValue( $loader, Container::get( 'controller.notification' ) );

		$property = $reflection->getProperty( 'rootPostLoader' );
		$property->setAccessible( true );
		$property->setValue( $loader, Container::get( 'loader.root_post' ) );

		return $loader;
	}

}
