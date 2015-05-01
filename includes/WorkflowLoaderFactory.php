<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Data\ManagerGroup;
use Flow\Exception\CrossWikiException;
use Flow\Exception\InvalidInputException;
use Flow\Exception\InvalidTopicUuidException;
use Flow\Exception\UnknownWorkflowIdException;
use Title;

class WorkflowLoaderFactory {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var BlockFactory
	 */
	protected $blockFactory;

	/**
	 * @var SubmissionHandler
	 */
	protected $submissionHandler;

	/**
	 * @var string
	 */
	protected $defaultWorkflowName;

	/**
	 * @var bool
	 */
	protected $pageMoveInProgress = false;

	/**
	 * @param ManagerGroup $storage
	 * @param BlockFactory $blockFactory
	 * @param SubmissionHandler $submissionHandler
	 * @param string $defaultWorkflowName
	 */
	function __construct(
		ManagerGroup $storage,
		BlockFactory $blockFactory,
		SubmissionHandler $submissionHandler,
		$defaultWorkflowName
	) {
		$this->storage = $storage;
		$this->blockFactory = $blockFactory;
		$this->submissionHandler = $submissionHandler;
		$this->defaultWorkflowName = $defaultWorkflowName;
	}

	public function pageMoveInProgress() {
		$this->pageMoveInProgress = true;
	}

	/**
	 * @param Title $pageTitle
	 * @param UUID|null $workflowId
	 * @return WorkflowLoader
	 * @throws InvalidInputException
	 * @throws CrossWikiException
	 */
	public function createWorkflowLoader( Title $pageTitle, $workflowId = null ) {
		if ( $pageTitle === null ) {
			throw new InvalidInputException( 'Invalid article requested', 'invalid-title' );
		}

		if ( $pageTitle && $pageTitle->isExternal() ) {
			throw new CrossWikiException( 'Interwiki to ' . $pageTitle->getInterwiki() . ' not implemented ', 'default' );
		}

		// @todo: ideally, workflowId is always set and this stuff is done in the places that call this
		if ( $workflowId === null ) {
			if ( $pageTitle->getNamespace() === NS_TOPIC ) {
				// topic page: workflow UUID is page title
				$workflowId = self::uuidFromTitle( $pageTitle );
			} else {
				// board page: workflow UUID is inside content model
				$page = \WikiPage::factory( $pageTitle );
				$content = $page->getContent();
				if ( $content instanceof BoardContent ) {
					$workflowId = $content->getWorkflowId();
				}
			}
		}

		if ( $workflowId === null ) {
			// no existing workflow found, create new one
			$workflow = Workflow::create( $this->defaultWorkflowName, $pageTitle );
		} else {
			$workflow = $this->loadWorkflowById( $pageTitle, $workflowId );
		}

		return new WorkflowLoader(
			$workflow,
			$this->blockFactory->createBlocks( $workflow ),
			$this->submissionHandler
		);
	}

	/**
	 * @param Title|false $title
	 * @param UUID $workflowId
	 * @return Workflow
	 * @throws InvalidInputException
	 */
	protected function loadWorkflowById( /* Title or false */ $title, $workflowId ) {
		/** @var Workflow $workflow */
		$workflow = $this->storage->getStorage( 'Workflow' )->get( $workflowId );
		if ( !$workflow ) {
			throw new UnknownWorkflowIdException( 'Invalid workflow requested by id', 'invalid-input' );
		}
		if ( $title !== false && $this->pageMoveInProgress === false && !$workflow->matchesTitle( $title ) ) {
			throw new InvalidInputException( 'Flow workflow is for different page', 'invalid-input' );
		}

		return $workflow;
	}

	/**
	 * Create a UUID for a Title object
	 *
	 * @param Title $title
	 * @return UUID
	 * @throws InvalidInputException When the Title does not represent a valid uuid
	 */
	public static function uuidFromTitle( Title $title ) {
		return self::uuidFromTitlePair( $title->getNamespace(), $title->getDbKey() );
	}

	/**
	 * Create a UUID for a ns/dbkey title pair
	 *
	 * @param integer $ns
	 * @param string $dbKey
	 * @return UUID
	 * @throws InvalidInputException When the pair does not represent a valid uuid
	 */
	public static function uuidFromTitlePair( $ns, $dbKey ) {
		if ( $ns !== NS_TOPIC ) {
			throw new InvalidInputException( "Title is not from NS_TOPIC: $ns", 'invalid-input' );
		}

		try {
			return UUID::create( strtolower( $dbKey ) );
		} catch ( InvalidInputException $e ) {
			throw new InvalidTopicUuidException( "$dbKey is not a valid UUID", 0, $e );
		}
	}
}
