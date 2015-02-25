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

/**
 * The import system uses a TalkpageImportOperation class.
 * This class is essentially a factory class that makes the
 * dependency injection less inconvenient for callers.
 */
class Importer {
	/** @var ManagerGroup **/
	protected $storage;
	/** @var WorkflowLoaderFactory **/
	protected $workflowLoaderFactory;
	/** @var LoggerInterface|null */
	protected $logger;
	/** @var BufferedCache */
	protected $cache;
	/** @var DbFactory */
	protected $dbFactory;
	/** @var bool */
	protected $allowUnknownUsernames;
	/** @var ProcessorGroup **/
	protected $postprocessors;
	/** @var SplQueue Callbacks for DeferredUpdate that are queue'd up by the commit process */
	protected $deferredQueue;

	public function __construct(
		ManagerGroup $storage,
		WorkflowLoaderFactory $workflowLoaderFactory,
		BufferedCache $cache,
		DbFactory $dbFactory,
		SplQueue $deferredQueue
	) {
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->cache = $cache;
		$this->dbFactory = $dbFactory;
		$this->postprocessors = new ProcessorGroup;
		$this->deferredQueue = $deferredQueue;
	}

	public function addPostprocessor( Postprocessor $proc ) {
		$this->postprocessors->add( $proc );
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param bool $allowed When true allow usernames that do not exist on the wiki to be
	 *  stored in the _ip field. *DO*NOT*USE* in any production setting, this is
	 *  to allow for imports from production wiki api's to test machines for
	 *  development purposes.
	 */
	public function setAllowUnknownUsernames( $allowed ) {
		$this->allowUnknownUsernames = (bool)$allowed;
	}

	/**
	 * Imports topics from a data source to a given page.
	 *
	 * @param IImportSource     $source
	 * @param Title             $targetPage
	 * @param ImportSourceStore $sourceStore
	 * @return bool True When the import completes with no failures
	 */
	public function import( IImportSource $source, Title $targetPage, ImportSourceStore $sourceStore ) {
		$operation = new TalkpageImportOperation( $source );
		return $operation->import( new PageImportState(
			$this->workflowLoaderFactory
				->createWorkflowLoader( $targetPage )
				->getWorkflow(),
			$this->storage,
			$sourceStore,
			$this->logger ?: new NullLogger,
			$this->cache,
			$this->dbFactory,
			$this->postprocessors,
			$this->deferredQueue,
			$this->allowUnknownUsernames
		) );
	}
}
