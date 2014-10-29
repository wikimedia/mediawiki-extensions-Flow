<?php

namespace Flow\Data\Listener;

use Flow\Exception\InvalidDataException;
use Flow\LinksTableUpdater;
use Flow\Data\LifecycleHandler;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\Reference;
use Flow\Parsoid\ReferenceExtractor;
use SplQueue;

/**
 * Listens for new revisions to be inserted.  Calculates the difference in
 * references(URLs, images, etc) between this new version and the previous
 * revision. Uses calculated difference to update links tables to match the new revision.
 */
class ReferenceRecorder implements LifecycleHandler {
	/**
	 * @var ReferenceExtractor
	 */
	protected $referenceExtractor;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var LinksTableUpdater
	 */
	protected $linksTableUpdater;

	/**
	 * @var SplQueue
	 */
	protected $deferredQueue;

	/**
	 * @param ReferenceExtractor $referenceExtractor
	 * @param LinksTableUpdater $linksTableUpdater
	 * @param ManagerGroup $storage
	 * @param SplQueue $deferredQueue
	 */
	public function __construct( ReferenceExtractor $referenceExtractor, LinksTableUpdater $linksTableUpdater, ManagerGroup $storage, SplQueue $deferredQueue ) {
		$this->referenceExtractor = $referenceExtractor;
		$this->linksTableUpdater = $linksTableUpdater;
		$this->storage = $storage;
		$this->deferredQueue = $deferredQueue;
	}

	public function onAfterLoad( $object, array $old ) {
		// Nuthin
	}

	public function onAfterInsert( $revision, array $new, array $metadata ) {
		if ( !isset( $metadata['workflow'] )) {
			return;
		}
		if ( !$revision instanceof AbstractRevision ) {
			throw new InvalidDataException( 'ReferenceRecorder can only attach to AbstractRevision storage');
		}
		$workflow = $metadata['workflow'];

		// Topic title is plain text, there is no reference to extract
		if ( $revision instanceof PostRevision && $revision->isTopicTitle() ) {
			return;
		}

		$this->deferredQueue->push( function() use ( $revision, $workflow ) {
			$prevReferences = $this->getExistingReferences( $revision->getRevisionType(), $revision->getCollectionId() );
			$references = $this->getReferencesFromRevisionContent( $workflow, $revision );

			list( $added, $removed ) = $this->referencesDifference( $prevReferences, $references );

			$this->storage->multiPut( $added );
			$this->storage->multiRemove( $removed );

			// Data updates
			$this->linksTableUpdater->doUpdate( $workflow );
		} );
	}

	/**
	 * Pulls references from a revision's content
	 *
	 * @param  Workflow $workflow The Workflow that the revision is attached to.
	 * @param  AbstractRevision $revision The Revision to pull references from.
	 * @return Reference[] Array of References.
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
	 *
	 * @param  string $revType The value returned from Revision::getRevisionType() for the revision.
	 * @param  UUID $objectId   The revision's Object ID.
	 * @return Reference[] Array of References.
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
	 *
	 * @param  Reference[] $old The old references.
	 * @param  Reference[] $new The new references.
	 * @return array Array with two elements: added and removed references.
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

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		// Nuthin
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		// Nuthin
	}
}
