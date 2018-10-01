<?php

namespace Flow\Utils;

use Exception;
use Flow\BoardMover;
use Flow\Content\BoardContent;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Exception\UnknownWorkflowIdException;
use Flow\WorkflowLoaderFactory;
use MediaWiki\Storage\RevisionStore;
use Title;

class InconsistentBoardFixer {

	/**
	 * @var DbFactory
	 */
	private $dbFactory;
	/**
	 * @var WorkflowLoaderFactory
	 */
	private $workflowLoaderFactory;
	/**
	 * @var BoardMover
	 */
	private $boardMover;
	/**
	 * @var ManagerGroup
	 */
	private $storage;
	/**
	 * @var RevisionStore
	 */
	private $revisionStore;
	private $dryRun;

	/**
	 * InconsistentBoardFixer constructor.
	 * @param DbFactory $dbFactory
	 * @param WorkflowLoaderFactory $workflowLoaderFactory
	 * @param BoardMover $boardMover
	 * @param ManagerGroup $storage
	 * @param RevisionStore $revisionStore
	 * @param bool $dryRun
	 */
	public function __construct(
		DbFactory $dbFactory,
		WorkflowLoaderFactory $workflowLoaderFactory,
		BoardMover $boardMover,
		ManagerGroup $storage,
		RevisionStore $revisionStore,
		$dryRun = false
	) {
		$this->dbFactory = $dbFactory;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->boardMover = $boardMover;
		$this->storage = $storage;
		$this->revisionStore = $revisionStore;
		$this->dryRun = $dryRun;
	}

	/**
	 * @param int $namespace
	 * @param string $pageTitle
	 * @param int $latestRevId
	 * @throws Exception
	 * @return array
	 */
	public function fix( $namespace, $pageTitle, $latestRevId ) {
		$result = [ 'output' => [], 'inconsistentCount' => false, 'fixableInconsistentCount' => false ];
		$coreTitle = Title::makeTitle( $namespace, $pageTitle );
		$revision = $this->revisionStore->getRevisionById( $latestRevId );
		$content = $revision->getContent( RevisionStore::RAW );
		if ( !$content instanceof BoardContent ) {
			$actualClass = get_class( $content );
			throw new Exception( "ERROR: '$coreTitle' content is a '$actualClass', but should be '"
								 . BoardContent::class . "'." );
		}
		$workflowId = $content->getWorkflowId();
		if ( is_null( $workflowId ) ) {
			// See T153320.  If the workflow exists, it could
			// be looked up by title/page ID and the JSON could
			// be fixed with an edit.
			// Otherwise, the core revision has to be deleted.  This
			// script does not do either of these things.
			throw new Exception( "ERROR: '$coreTitle' JSON content does not have a valid workflow ID." );
		}
		$workflowIdAlphadecimal = $workflowId->getAlphadecimal();

		try {
			$workflow = $this->workflowLoaderFactory->loadWorkflowById( false, $workflowId );
		} catch ( UnknownWorkflowIdException $ex ) {
			// This is a different error (a core page refers to
			// a non-existent workflow), which this script can not fix.
			throw new Exception( "ERROR: '$coreTitle' refers to workflow ID " .
								 "'$workflowIdAlphadecimal', which could not be found." );
		}
		if ( !$workflow->matchesTitle( $coreTitle ) ) {
			$pageId = $latestRevId;

			$workflowTitle = $workflow->getOwnerTitle();
			$result['output'][] = "INCONSISTENT: Core title for '$workflowIdAlphadecimal' is " .
								  "'$coreTitle', but Flow title is '$workflowTitle'\n";

			$result['inconsistentCount'] = true;

			// Sanity check, or this will fail in BoardMover
			$workflowByPageId = $this->storage->find( 'Workflow', [
				'workflow_wiki' => wfWikiID(),
				'workflow_page_id' => $pageId,
			] );

			if ( !$workflowByPageId ) {
				throw new Exception( "ERROR: '$coreTitle' has page ID '$pageId', but no workflow " .
									 "is linked to this page ID" );
			}

			$result['fixableInconsistentCount'] = true;

			if ( !$this->dryRun ) {
				$this->boardMover->move( $pageId, $coreTitle );
				$this->boardMover->commit();
				$result['output'][] = "FIXED: Updated '$workflowIdAlphadecimal' to match core " .
									  "title, '$coreTitle'\n";
			}

		}
		return $result;
	}

}
