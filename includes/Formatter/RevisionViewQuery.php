<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use User;

abstract class RevisionViewQuery extends AbstractQuery {

	/**
	 * Create a revision based on revisionId
	 * @param UUID|string
	 * @return AbstractRevision
	 */
	abstract protected function createRevision( $revId );

	/**
	 * Get the block name for current revision query
	 * @return string
	 */
	abstract protected function getBlockName();

	/**
	 * Get the diff link action for current revision type
	 */
	abstract protected function getDiffAction();

	/**
	 * Get the data for rendering single revision view
	 * @param string
	 * @return RevisionViewRow
	 * @throws InvalidInput
	 */
	public function getSingleViewResult( $revId ) {
		if ( !$revId ) {
			throw new InvalidInputException( 'Missing revision', 'missing-revision' );
		}
		$rev = $this->createRevision( $revId );
		if ( !$rev ) {
			throw new InvalidInputException( 'Could not find revision: ' . $revId, 'missing-revision' );
		}
		$this->loadMetadataBatch( array( $rev ) );
		$row = new RevisionViewRow();
		$row->blockName = $this->getBlockName();
		$row->diffAction = $this->getDiffAction();
		return $this->buildResult( $rev, null, $row );
	}

	/**
	 * Get the data for rendering revisions diff view
	 * @param string
	 * @param UUID
	 * @param UUID
	 * @return RevisionViewRow
	 */
	public function getDiffViewResult( $curId, $prevId = null ) {
		$cur = $this->createRevision( $curId );
		if ( !$cur ) {
			throw new InvalidInputException( 'Could not find revision: ' . $curId, 'missing-revision' );
		}
		if ( !$prevId ) {
			$prevId = $cur->getPrevRevisionId();
		}
		$prev = $this->createRevision( $prevId );
		if ( !$prev ) {
			throw new InvalidInputException( 'Could not find revision to compare against: ' . $curId->getAlphadecimal(), 'missing-revision' );
		}
		if ( !$this->isComparable( $cur, $prev ) ) {
			throw new InvalidInputException( 'Attempt to compare revisions of different types', 'revision-comparison' );
		}

		// Re-position old and new revisions if necessary
		if (
			$cur->getRevisionId()->getTimestamp() >
			$prev->getRevisionId()->getTimestamp()
		) {
			$oldRev = $prev;
			$newRev = $cur;
		} else {
			$oldRev = $cur;
			$newRev = $prev;
		}

		$permission = Container::get( 'permissions' );
		// Todo - Check the permission before invoking this function?
		if ( !$permission->isAllowed( $oldRev, 'view' ) || !$permission->isAllowed( $newRev, 'view' ) ) {
			throw new PermissionException( 'Insufficient permission to compare revisions', 'insufficient-permission' );
		}

		$this->loadMetadataBatch( array( $oldRev, $newRev ) );
		$row = new RevisionViewRow();
		$row->blockName = $this->getBlockName();
		$row->diffAction = $this->getDiffAction();
		$row = $this->buildResult( $newRev, null, $row );

		$old = new RevisionViewRow();
		$old->blockName = $this->getBlockName();
		$old->diffAction = $this->getDiffAction();
		$old = $this->buildResult( $oldRev, null, $old );

		return array( $row, $old );
	}

	public function isComparable( AbstractRevision $cur, AbstractRevision $prev ) {
		if ( $cur->getRevisionType() == $prev->getRevisionType() ) {
			return $cur->getCollectionId()->equals( $prev->getCollectionId() );
		} else {
			return false;
		}
	}
}

class HeaderViewQuery extends RevisionViewQuery {

	/**
	 * {@inheritDoc}
	 */
	protected function createRevision( $revId ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}
		return $this->storage->get(
			'Header',
			$revId
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getBlockName() {
		return 'header';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getDiffAction() {
		return 'compare-header-revisions';
	}

}

class PostViewQuery extends RevisionViewQuery {

	/**
	 * {@inheritDoc}
	 */
	protected function createRevision( $revId ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}
		return $this->storage->get(
			'PostRevision',
			$revId
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getBlockName() {
		return 'header';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getDiffAction() {
		return 'compare-post-revisions';
	}
}

class PostSummaryViewQuery extends RevisionViewQuery {

	/**
	 * {@inheritDoc}
	 */
	protected function createRevision( $revId ) {
		if ( !$revId instanceof UUID ) {
			$revId = UUID::create( $revId );
		}
		return $this->storage->get(
			'PostSummary',
			$revId
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getBlockName() {
		return 'topicsummary';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getDiffAction() {
		return 'compare-postsummary-revisions';
	}

}
