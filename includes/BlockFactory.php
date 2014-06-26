<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Block\HeaderBlock;
use Flow\Block\TopicBlock;
use Flow\Block\TopicListBlock;
use Flow\Block\TopicSummaryBlock;
use Flow\Block\BoardHistoryBlock;
use Flow\Model\Definition;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Data\RootPostLoader;
use Flow\Exception\InvalidInputException;
use Flow\Exception\InvalidDataException;

class BlockFactory {

	public function __construct(
		ManagerGroup $storage,
		NotificationController $notificationController,
		RootPostLoader $rootPostLoader
	) {
		$this->storage = $storage;
		$this->notificationController = $notificationController;
		$this->rootPostLoader = $rootPostLoader;
	}

	/**
     * @param Definition $definition
     * @param Workflow $workflow
	 * @return AbstractBlock[]
	 * @throws InvalidInputException When the definition type is unrecognized
	 * @throws InvalidDataException When multiple blocks share the same name
	 */
	public function createBlocks( Definition $definition, Workflow $workflow ) {
		switch( $definition->getType() ) {
			case 'discussion':
				$blocks = array(
					new HeaderBlock( $workflow, $this->storage, $this->notificationController ),
					new TopicListBlock( $workflow, $this->storage, $this->notificationController ),
					new BoardHistoryBlock( $workflow, $this->storage, $this->notificationController ),
				);
				break;

			case 'topic':
				$blocks = array(
					new TopicBlock( $workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
					new TopicSummaryBlock( $workflow, $this->storage, $this->notificationController, $this->rootPostLoader ),
				);
				break;

			default:
				throw new InvalidInputException( 'Not Implemented', 'invalid-definition' );
				break;
		}

		$return = array();
		/** @var AbstractBlock[] $blocks */
		foreach ( $blocks as $block ) {
			if ( !isset( $return[$block->getName()] ) ) {
				$return[$block->getName()] = $block;
			} else {
				throw new InvalidDataException( 'Multiple blocks with same name is not yet supported', 'fail-load-data' );
			}
		}

		return $return;
	}
}
