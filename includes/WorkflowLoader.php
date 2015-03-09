<?php

namespace Flow;

use Flow\Block\Block;
use Flow\Model\Workflow;
use IContextSource;

class WorkflowLoader {
	/**
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @var Block[]
	 */
	protected $blocks;

	/**
	 * @var SubmissionHandler
	 */
	protected $submissionHandler;

	/**
	 * @param Workflow $workflow
	 * @param Block[] $blocks
	 * @param SubmissionHandler $submissionHandler
	 */
	public function __construct(
			Workflow $workflow,
			array $blocks,
			SubmissionHandler $submissionHandler
	) {
		$this->blocks = $blocks;
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
	 * @return Block[]
	 */
	public function getBlocks() {
		return $this->blocks;
	}

	/**
	 * @param IContextSource $context
	 * @param string $action
	 * @param array $parameters
	 * @return Block[]
	 */
	public function handleSubmit( IContextSource $context, $action, array $parameters ) {
		return $this->submissionHandler
			->handleSubmit( $this->workflow, $context, $this->blocks, $action, $parameters );
	}

	public function commit( array $blocks ) {
		return $this->submissionHandler->commit( $this->workflow, $blocks );
	}
}
