<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Model\Workflow;
use WebRequest;

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

	public function handleSubmit( $action, array $blocks, $user, WebRequest $request ) {
		return $this->submissionHandler->handleSubmit( $this->workflow, $action, $blocks, $user, $request );
	}

	public function commit( Workflow $workflow, array $blocks ) {
		return $this->submissionHandler->commit( $workflow, $blocks );
	}

	public function extractBlockParameters( WebRequest $request, array $blocks ) {
		return $this->submissionHandler->extractBlockParameters( $request, $blocks );
	}
}
