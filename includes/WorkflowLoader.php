<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Model\Workflow;
use IContextSource;

class WorkflowLoader {
	/**
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @var BlockFactory
	 */
	protected $blockFactory;

	/**
	 * @var SubmissionHandler
	 */
	protected $submissionHandler;

	/**
	 * @param Workflow $workflow
	 * @param BlockFactory $blockFactory
	 * @param SubmissionHandler $submissionHandler
	 */
	public function __construct(
			Workflow $workflow,
			BlockFactory $blockFactory,
			SubmissionHandler $submissionHandler
	) {
		$this->blockFactory = $blockFactory;
		$this->submissionHandler = $submissionHandler;
		$this->workflow = $workflow;
	}

	/**
	 * @return Workflow
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * @return AbstractBlock[]
	 */
	public function createBlocks() {
		return $this->blockFactory->createBlocks( $this->workflow );
	}

	/**
	 * @param IContextSource $context
	 * @param array $blocks
	 * @param string $action
	 * @param array $parameters
	 * @return Block\AbstractBlock[]
	 */
	public function handleSubmit( IContextSource $context, array $blocks, $action, array $parameters ) {
		return $this->submissionHandler
			->handleSubmit( $this->workflow, $context, $blocks, $action, $parameters );
	}

	public function commit( Workflow $workflow, array $blocks ) {
		return $this->submissionHandler->commit( $workflow, $blocks );
	}
}
