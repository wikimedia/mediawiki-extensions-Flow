<?php

namespace Flow\Import;

use DeferredUpdates;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Import\Postprocessor\Postprocessor;
use Flow\Import\Postprocessor\ProcessorGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\WorkflowLoaderFactory;
use IP;
use MWCryptRand;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use SplQueue;
use Title;
use UIDGenerator;
use User;

class TopicImportState {
	/**
	 * @var PageImportState
	 */
	public $parent;

	/**
	 * @var Workflow
	 */
	public $topicWorkflow;

	/**
	 * @var PostRevision
	 */
	public $topicTitle;

	/**
	 * @var string
	 */
	protected $lastModified;

	public function __construct(
		PageImportState $parent,
		Workflow $topicWorkflow,
		PostRevision $topicTitle
	) {
		$this->parent = $parent;
		$this->topicWorkflow = $topicWorkflow;
		$this->topicTitle = $topicTitle;

		$this->workflowModifiedProperty = new ReflectionProperty( 'Flow\\Model\\Workflow', 'lastModified' );
		$this->workflowModifiedProperty->setAccessible( true );

		$this->lastModified = '';
		$this->recordModificationTime( $topicWorkflow->getId() );
	}

	public function getMetadata() {
		return array(
			'workflow' => $this->topicWorkflow,
			'board-workflow' => $this->parent->boardWorkflow,
			'topic-title' => $this->topicTitle,
		);
	}

	/**
	 * Notify the state about a modification action at a given time.
	 *
	 * @param UUID $uuid UUID of the modification revision.
	 */
	public function recordModificationTime( UUID $uuid ) {
		$timestamp = $uuid->getTimestamp();
		$timestamp = wfTimestamp( TS_MW, $timestamp );

		if ( $timestamp > $this->lastModified ) {
			$this->lastModified = $timestamp;
		}
	}

	/**
	 * Saves the last modified timestamp based on calls to recordModificationTime
	 * XXX: Kind of icky; reaching through the parent and doing a second put().
	 */
	public function commitLastModified() {
		$this->workflowModifiedProperty->setValue(
			$this->topicWorkflow,
			$this->lastModified
		);

		$this->parent->put( $this->topicWorkflow, $this->getMetadata() );
	}
}
