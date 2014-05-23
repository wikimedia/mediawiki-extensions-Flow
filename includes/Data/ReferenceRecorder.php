<?php

namespace Flow\Data;

use Flow\LinksTableUpdater;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Parsoid\ReferenceExtractor;

class ReferenceRecorder implements LifecycleHandler {
	protected $referenceExtractor, $storage, $linksTableUpdater;

	function __construct( ReferenceExtractor $referenceExtractor, LinksTableUpdater $linksTableUpdater, ManagerGroup $storage ) {
		$this->referenceExtractor = $referenceExtractor;
		$this->linksTableUpdater = $linksTableUpdater;
		$this->storage = $storage;
	}

	function onAfterLoad( $object, array $old ) {
		// Nuthin
	}

	function onAfterInsert( $revision, array $new ) {
		$workflowId = $revision->getCollection()->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );

		if ( ! $workflow ) {
			return;
		}

		$prevReferences = $this->getExistingReferences( $revision->getRevisionType(), $revision->getCollectionId() );
		$references = $this->getReferencesFromRevisionContent( $workflow, $revision );

		list( $added, $removed ) = $this->referencesDifference( $prevReferences, $references );

		$this->storage->multiPut( $added );
		$this->storage->multiRemove( $removed );

		// Data updates
		$this->linksTableUpdater->doUpdate( $workflow );
	}

	/**
	 * Pulls references from a revision's content
	 * @param  Workflow         $workflow The Workflow that the revision is attached to.
	 * @param  AbstractRevision $revision The Revision to pull references from.
	 * @return array Array of References.
	 */
	public function getReferencesFromRevisionContent( Workflow $workflow, AbstractRevision $revision ) {
		$content = $revision->getContent( 'html' );

		return $this->referenceExtractor->getReferences(
			$workflow,
			$revision->getRevisionType(),
			$revision->getCollectionId(),
			$content
		);
	}

	/**
	 * Retrieves references that are already stored in the database for a given revision
	 * @param  string $revType The value returned from Revision::getRevisionType() for the revision.
	 * @param  UUID   $objectId   The revision's Object ID.
	 * @return array           Array of References.
	 */
	public function getExistingReferences( $revType, UUID $objectId ) {
		$prevWikiReferences = $this->storage->find( 'WikiReference', array(
			'ref_src_object_type' => $revType,
			'ref_src_object_id' => $objectId,
		) );

		$prevUrlReferences = $this->storage->find( 'URLReference', array(
			'ref_src_object_type' => $revType,
			'ref_src_object_id' => $objectId,
		) );

		return array_merge( (array) $prevWikiReferences, (array) $prevUrlReferences );
	}

	/**
	 * Compares two arrays of references
	 *
	 * Would be protected if not for testing.
	 * @param  array  $old The old references.
	 * @param  array  $new The new references.
	 * @return array       Array with two elements: added and removed references.
	 */
	public function referencesDifference( array $old, array $new ) {
		$newReferences = array();

		foreach( $new as $ref ) {
			$newReferences[$ref->getIdentifier()] = $ref;
		}

		$oldReferences = array();

		foreach( $old as $ref ) {
			$oldReferences[$ref->getIdentifier()] = $ref;
		}

		$addReferences = array();

		foreach( $newReferences as $identifier => $ref ) {
			if ( ! isset( $oldReferences[$identifier] ) ) {
				$addReferences[] = $ref;
			}
		}

		$removeReferences = array();

		foreach( $oldReferences as $identifier => $ref ) {
			if ( ! isset( $newReferences[$identifier] ) ) {
				$removeReferences[] = $ref;
			}
		}

		return array( $addReferences, $removeReferences );
	}

	function onAfterUpdate( $object, array $old, array $new ) {
		// Nuthin
	}

	function onAfterRemove( $object, array $old ) {
		// Nuthin
	}
}
