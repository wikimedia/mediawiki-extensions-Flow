<?php

namespace Flow\Data\Listener;

use Flow\Exception\InvalidDataException;
use Flow\LinksTableUpdater;
use Flow\Data\LifecycleHandler;
use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Model\Reference;
use Flow\Parsoid\ReferenceExtractor;
use Flow\Repository\TreeRepository;

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
	 * @var TreeRepository Used to query for the posts within a topic when moderation
	 *  changes the visibility of a topic.
	 */
	protected $treeRepository;

	public function __construct(
		ReferenceExtractor $referenceExtractor,
		LinksTableUpdater $linksTableUpdater,
		ManagerGroup $storage,
		TreeRepository $treeRepository
	) {
		$this->referenceExtractor = $referenceExtractor;
		$this->linksTableUpdater = $linksTableUpdater;
		$this->storage = $storage;
		$this->treeRepository = $treeRepository;
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

		if ( $revision instanceof PostRevision && $revision->isTopicTitle() ) {
			list( $added, $removed ) = $this->calculateChangesFromTopic( $workflow, $revision );
		} else {
			list( $added, $removed ) = $this->calculateChangesFromExisting( $workflow, $revision );
		}

		$this->storage->multiPut( $added );
		$this->storage->multiRemove( $removed );

		// Data updates
		$this->linksTableUpdater->doUpdate( $workflow );
	}

	/**
	 * Compares the references contained within $revision against those stored for
	 * that revision.  Returns the differences.
	 *
	 * @param Workflow $workflow
	 * @param AbstractRevision $revision
	 * @return array Two nested arrays, first the references that were added and
	 *  second the references that were removed.
	 */
	protected function calculateChangesFromExisting(
		Workflow $workflow,
		AbstractRevision $revision,
		PostRevision $root = null
	) {
		$prevReferences = $this->getExistingReferences(
			$revision->getRevisionType(),
			$revision->getCollectionId()
		);
		$references = $this->getReferencesFromRevisionContent( $workflow, $revision, $root );

		return $this->referencesDifference( $prevReferences, $references );
	}

	/**
	 * While topic's themselves are plaintext and do not contain any references,
	 * moderation actions change what references are visible.  When transitioning
	 * from or to a generically visible state (unmoderated or locked) the entire
	 * topic + summary needs to be re-evaluated.
	 *
	 * @param PostRevision $revision Topic revision object that was inserted
	 * @param array $new Database row to be inserted for topic
	 * @param array $metadata Commit metadata for the topic
	 * @return array Contains two arrays, first the references to add a second
	 *  the references to remove
	 */
	protected function calculateChangesFromTopic( Workflow $workflow, PostRevision $current ) {
		if ( $current->isFirstRevision() ) {
			return array( array(), array() );
		}
		$previous = $this->storage->get( 'PostRevision', $current->getPrevRevisionId() );
		if ( !$previous ) {
			throw new FlowException( 'Expcted previous revision of ' . $current->getPrevRevisionId()->getAlphadecimal() );
		}

		$isHidden = $this->isHidden( $current );
		$wasHidden = $this->isHidden( $previous );

		if ( $isHidden === $wasHidden ) {
			return array( array(), array() );
		}

		// re-run
		$revisions = $this->collectTopicRevisions( $workflow );
		$added = array();
		$removed = array();
		foreach ( $revisions as $revision ) {
			list( $add, $remove ) = $this->calculateChangesFromExisting( $workflow, $revision, $current );
			$added = array_merge( $added, $add );
			$removed = array_merge( $removed, $remove );
		};

		return array( $added, $removed );
	}

	protected function isHidden( PostRevision $revision ) {
		return $revision->isModerated() && $revision->getModerationState() !== $revision::MODERATED_LOCKED;
	}

	/**
	 * Gets all the 'top' revisions within the topic, namely the posts and the
	 * summary. These are used when a topic changes is visibility via moderation
	 * to add or remove the relevant references.
	 *
	 * @param Workflow $workflow
	 * @return AbstractRevision[]
	 */
	protected function collectTopicRevisions( Workflow $workflow ) {
		$postIds = reset( $this->treeRepository->fetchSubtreeNodeList( array( $workflow->getId() ) ) );
		$queries = array();
		foreach ( $postIds as $uuid ) {
			$queries[] = array( 'rev_type_id' => $uuid );
		}

		$posts = $this->storage->findMulti(
			'PostRevision',
			$queries,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		// we also need the most recent topic summary if it exists
		$summaries = $this->storage->find(
			'PostSummary',
			array( 'rev_type_id' => $workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		$result = $summaries;
		// we have to unwrap the posts since we used findMulti, it returns
		// a separate result set for each query
		foreach ( $posts as $found ) {
			$result[] = reset( $found );
		}
		return $result;
	}

	/**
	 * Pulls references from a revision's content
	 *
	 * @param  Workflow $workflow The Workflow that the revision is attached to.
	 * @param  AbstractRevision $revision The Revision to pull references from.
	 * @return Reference[] Array of References.
	 */
	public function getReferencesFromRevisionContent(
		Workflow $workflow,
		AbstractRevision $revision,
		PostRevision $root = null
	) {
		// Locked is the only moderated state we still collect references for.
		if ( $revision->isModerated() && !$revision->isLocked() ) {
			return array();
		}

		// If this is attached to a topic we also need to check its permissions
		if ( $root === null ) {
			if ( $revision instanceof PostRevision && !$revision->isTopicTitle() ) {
				$root = $revision->getCollection()->getRoot()->getLastRevision();
			} elseif ( $revision instanceof PostSummary ) {
				$root = $revision->getCollection()->getPost()->getRoot()->getLastRevision();
			}
		}

		if ( $root && ( $root->isModerated() && !$root->isLocked() ) ) {
			return array();
		}

		return $this->referenceExtractor->getReferences(
			$workflow,
			$revision->getRevisionType(),
			$revision->getCollectionId(),
			$revision->getContent( 'html' )
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
