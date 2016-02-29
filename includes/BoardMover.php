<?php

namespace Flow;

use DatabaseBase;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Model\Header;
use Flow\Model\Workflow;
use Title;
use User;

class BoardMover {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var User
	 */
	protected $nullEditUser;

	/**
	 * @var DatabaseBase|null
	 */
	protected $dbw;

	public function __construct( DbFactory $dbFactory, BufferedCache $cache, ManagerGroup $storage, User $nullEditUser ) {
		$this->dbFactory = $dbFactory;
		$this->cache = $cache;
		$this->storage = $storage;
		$this->nullEditUser = $nullEditUser;
	}

	/**
	 * Collects the workflow and header (if it exists) and puts them into the database. Does
	 * not commit yet. It is intended for prepareMove to be called from the TitleMove hook,
	 * and committed from TitleMoveComplete hook. This ensures that if some error prevents the
	 * core transaction from committing this transaction is also not committed.
	 *
	 * @param int $oldPageId Page ID before move/change
	 * @param Title $newPage Page after move/change
	 * @throws Exception\DataModelException
	 * @throws FlowException
	 */
	public function prepareMove( $oldPageId, Title $newPage ) {
		if ( $this->dbw !== null ) {
			throw new FlowException( "Already prepared for move from {$oldPageId} to {$newPage->getArticleID()}" );
		}

		// All reads must go through master to help ensure consistency
		$this->dbFactory->forceMaster();

		// Open a transaction, this will be closed from self::commit.
		$this->dbw = $this->dbFactory->getDB( DB_MASTER );
		$this->dbw->startAtomic( __CLASS__ );
		$this->cache->begin();

		// @todo this loads every topic workflow this board has ever seen,
		// would prefer to update db directly but that won't work due to
		// the caching layer not getting updated.  After dropping Flow\Data\Index\*
		// revisit this.
		/** @var Workflow[] $found */
		$found = $this->storage->find( 'Workflow', array(
			'workflow_wiki' => wfWikiId(),
			'workflow_page_id' => $oldPageId,
		) );
		if ( !$found ) {
			throw new FlowException( "Could not locate workflow for $oldPageId" );
		}

		$discussionWorkflow = null;
		foreach ( $found as $workflow ) {
			if ( $workflow->getType() === 'discussion' ) {
				$discussionWorkflow = $workflow;
			}
			$workflow->updateFromPageId( $oldPageId, $newPage );
			$this->storage->put( $workflow, array() );
		}
		if ( $discussionWorkflow === null ) {
			throw new FlowException( "Main discussion workflow for $oldPageId not found" );
		}

		/** @var Header[] $found */
		$found = $this->storage->find(
			'Header',
			array( 'rev_type_id' => $discussionWorkflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		if ( $found ) {
			$header = reset( $found );
			$nextHeader = $header->newNextRevision(
				$this->nullEditUser,
				$header->getContentRaw(),
				$header->getContentFormat(),
				'edit-header',
				$newPage
			);
			$this->storage->put( $nextHeader, array(
				'workflow' => $discussionWorkflow,
			) );
		}
	}

	/**
	 * @throws Exception\FlowException
	 */
	public function commit() {
		if ( $this->dbw === null ) {
			throw new FlowException( 'Board move not prepared.');
		}

		try {
			$this->dbw->endAtomic( __CLASS__ );
			$this->cache->commit();
		} catch ( \Exception $e ) {
			$this->dbw->rollback( __METHOD__ );
			$this->cache->rollback();
			throw $e;
		}
	}
}
