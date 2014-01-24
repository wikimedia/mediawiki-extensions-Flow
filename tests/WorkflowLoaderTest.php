<?php

namespace Flow\Tests;

use Flow\Container;
use ReflectionClass;

class WorkflowLoaderTest extends \MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();
	}

	/**
	 * definition and a list of corresponding expected blocks
	 */
	public static function provideDataCreateBlocks() {
		return array(
			array( 'board-history', array( 'Flow\Block\BoardHistoryBlock' ) ),
			array( 'header-view', array( 'Flow\Block\HeaderBlock' ) ),
			array( 'bad-definition', array(
				'Flow\Block\HeaderBlock',
				'Flow\Block\TopicListBlock',
			) ),
		);
	}

	/**
	 * @dataProvider provideDataCreateBlocks
	 */
	public function testCreateBlocks( $definition, $blocks ) {
		Container::get( 'request' )->setVal( 'definition', $definition );
		$workflowLoader = $this->mockWorkflowLoader( 'discussion' );
		$res = $workflowLoader->createBlocks();
		$resultBlocks = array();
		foreach ( $res as $obj ) {
			$resultBlocks[] = get_class( $obj );
		}
		$this->assertEquals( $resultBlocks, $blocks );
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
	 * @expectedException \Flow\Exception\InvalidDataException
	 */
	public function testCreateBlocksWithInvalidDataException() {
		// Trigger InvalidDataException
		$container = Container::getContainer();
		$container['definitions'] = array(
			'discussion' => array(
				'blocks' => array(
					'\Flow\Block\HeaderBlock',
					'\Flow\Block\HeaderBlock',
					'\Flow\Block\TopicListBlock',
				),
			),
		);
		$workflowLoader = $this->mockWorkflowLoader( 'discussion' );
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
