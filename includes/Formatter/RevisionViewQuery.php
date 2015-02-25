<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Exception\InvalidInputException;
use Flow\Exception\PermissionException;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;

abstract class RevisionViewQuery extends AbstractQuery {

	/**
	 * Create a revision based on revisionId
	 * @param UUID|string
	 * @return AbstractRevision
	 */
	abstract protected function createRevision( $revId );

	/**
	 * Get the data for rendering single revision view
	 * @param string
	 * @return FormatterRow
	 * @throws InvalidInputException
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
		return $this->buildResult( $rev, null );
	}

	/**
	 * Get the data for rendering revisions diff view
	 * @param UUID $curId
	 * @param UUID|null $prevId
	 * @return FormatterRow[]
	 * @throws InvalidInputException
	 * @throws PermissionException
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

		/** @var RevisionActionPermissions $permission */
		$permission = Container::get( 'permissions' );
		// Todo - Check the permission before invoking this function?
		if ( !$permission->isAllowed( $oldRev, 'view' ) || !$permission->isAllowed( $newRev, 'view' ) ) {
			throw new PermissionException( 'Insufficient permission to compare revisions', 'insufficient-permission' );
		}

		$this->loadMetadataBatch( array( $oldRev, $newRev ) );

		return array(
			$this->buildResult( $newRev, null ),
			$this->buildResult( $oldRev, null ),
		);
	}

	public function isComparable( AbstractRevision $cur, AbstractRevision $prev ) {
		if ( $cur->getRevisionType() == $prev->getRevisionType() ) {
			return $cur->getCollectionId()->equals( $prev->getCollectionId() );
		} else {
			return false;
		}
	}
}

