<?php

namespace Flow\Data;

use Flow\LinksTableUpdater;
use Flow\ReferenceExtractor;

// Required tests:
// * Add post with references
// * Add references to header
// * Remove references from post
// * Check that output is expected
// * Re-add references to another post
// * Remove some from header
// * Check output

class ReferenceRecorder implements LifeCycleHandler {
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
		$content = $revision->getContent( 'html' );

		$workflowId = $revision->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );

		$references = $this->referenceExtractor->getReferences(
			$workflow,
			$revision->getRevisionType(),
			$revision->getCollectionId(),
			$content
		);

		$prevWikiReferences = $this->storage->find( 'WikiReference', array(
			'ref_src_object_type' => $revision->getRevisionType(),
			'ref_src_object_id' => $revision->getObjectId(),
		) );
		$prevUrlReferences = $this->storage->find( 'URLReference', array(
			'ref_src_object_type' => $revision->getRevisionType(),
			'ref_src_object_id' => $revision->getObjectId(),
		) );

		$prevReferences = array_merge( $prevWikiReferences, $prevUrlReferences );

		list( $added, $removed ) = $this->referencesDifference( $prevReferences, $references );

		$this->linksTableUpdater->updateLinksTables( $revision->getObjectId(), $workflow->getArticleTitle(), $added, $removed );

		$this->storage->multiPut( $added );
		$this->storage->multiRemove( $removed );
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