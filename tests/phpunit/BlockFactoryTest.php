<?php

namespace Flow\Tests;

use Flow\BlockFactory;
use Flow\Data\ManagerGroup;
use Flow\Model\Workflow;
use Flow\Repository\RootPostLoader;

/**
 * @covers \Flow\BlockFactory
 *
 * @group Flow
 */
class BlockFactoryTest extends FlowTestCase {

	public function provideDataCreateBlocks() {
		return [
			[
				'discussion',
				[
					\Flow\Block\HeaderBlock::class,
					\Flow\Block\TopicListBlock::class,
					\Flow\Block\BoardHistoryBlock::class,
				]
			],
			[
				'topic',
				[
					\Flow\Block\TopicBlock::class,
					\Flow\Block\TopicSummaryBlock::class,
				]
			],
		];
	}

	/**
	 * @covers \Flow\Block\AbstractBlock::__construct
	 * @covers \Flow\Block\BoardHistoryBlock::__construct
	 * @covers \Flow\Block\HeaderBlock::__construct
	 * @covers \Flow\Block\TopicBlock::__construct
	 * @covers \Flow\Block\TopicListBlock::__construct
	 * @covers \Flow\Block\TopicSummaryBlock::__construct
	 * @dataProvider provideDataCreateBlocks
	 */
	public function testCreateBlocks( $workflowType, array $expectedResults ) {
		$factory = $this->createBlockFactory();
		$workflow = $this->mockWorkflow( $workflowType );

		$blocks = $factory->createBlocks( $workflow );
		$this->assertCount( count( $blocks ), $expectedResults );

		$results = [];
		foreach ( $blocks as $obj ) {
			$results[] = get_class( $obj );
		}
		$this->assertEquals( $expectedResults, $results );
	}

	public function testCreateBlocksWithInvalidInputException() {
		$factory = $this->createBlockFactory();
		$workflow = $this->mockWorkflow( 'a-bad-database-flow-workflow' );
		$this->expectException( \Flow\Exception\DataModelException::class );
		$factory->createBlocks( $workflow );
	}

	private function createBlockFactory(): BlockFactory {
		return new BlockFactory(
			$this->createMock( ManagerGroup::class ),
			$this->createMock( RootPostLoader::class )
		);
	}

	private function mockWorkflow( $type ): Workflow {
		$workflow = $this->createMock( Workflow::class );
		$workflow->method( 'getType' )
			->willReturn( $type );

		return $workflow;
	}
}
