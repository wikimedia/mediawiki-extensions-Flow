<?php

namespace Flow;

use Flow\Data\ManagerGroup;
use Flow\Model\Reference;
use Flow\Model\WikiReference;
use Flow\Exception\CrossWikiException;
use Flow\Model\UUID;
use Title;

class ReferenceClarifier {
	protected $storage, $urlGenerator;
	protected $referenceCache;

	function __construct( ManagerGroup $storage, UrlGenerator $urlGenerator ) {
		$this->storage = $storage;
		$this->urlGenerator = $urlGenerator;
		$this->referenceCache = array();
	}

	public function getWhatLinksHereProps( $row, Title $from, Title $to ) {
		$ids = array();
		$props = array();
		$references = $this->getWikiReferences( $from, $to );

		// Collect referenced workflow ids and load them so we can generate
		// links to their pages
		foreach( $references as $reference ) {
			$id = $reference->getWorkflowId();
			// utilize array key to de-duplicate
			$ids[$id->getAlphadecimal()] = $id;
		}

		// Don't need to do anything with them, they are automatically
		// passed into url generator when loaded.
		$this->storage->getMulti( 'Workflow', $ids );

		// Messages that can be used here:
		// * flow-whatlinkshere-header
		// * flow-whatlinkshere-post
		// * flow-whatlinkshere-post-summary
		// Topic is plain text and do not have links.
		foreach( $references as $reference ) {
			if ( $reference->getType() === WikiReference::TYPE_CATEGORY ) {
				// While it might make sense to have backlinks from categories to
				// a page in what links here, thats not what mediawiki currently does.
				continue;
			}
			try {
				$url = $this->getObjectLink( $reference->getWorkflowId(), $reference->getObjectType(), $reference->getObjectId() );
				$props[] = wfMessage( 'flow-whatlinkshere-' . $reference->getObjectType(), $url )->parse();
			} catch ( CrossWikiException $e ) {
				// Ignore expected cross-wiki exception.
				// Gerrit 136280 would add a wiki field to the query in
				// loadReferencesForPage(), we can remove catching the exception
				// in here once it's merged
			}
		}

		return $props;
	}

	/**
	 * @param Title $from
	 * @param Title $to
	 * @return WikiReference[]
	 */
	public function getWikiReferences( Title $from, Title $to ) {
		if ( ! isset( $this->referenceCache[$from->getPrefixedDBkey()] ) ) {
			$this->loadReferencesForPage( $from );
		}

		$fromT = $from->getPrefixedDBkey();
		$toT = 'title:' . $to->getPrefixedDBkey();

		return isset( $this->referenceCache[$fromT][$toT] )
			? $this->referenceCache[$fromT][$toT]
			: array();
	}

	/**
	 * @param UUID $workflow
	 * @param string $objectType
	 * @param UUID $objectId
	 * @return string Full URL
	 */
	protected function getObjectLink( UUID $workflow, $objectType, UUID $objectId ) {
		if ( $objectType === 'post' ) {
			$anchor = $this->urlGenerator->postLink( null, $workflow, $objectId );
		} elseif ( $objectType === 'header' ) {
			$anchor = $this->urlGenerator->workflowLink( null, $workflow );
		} else {
			wfDebugLog( 'Flow', __METHOD__ . ": Unknown \$objectType: $objectType" );
			$anchor = $this->urlGenerator->workflowLink( null, $workflow );
		}

		return $anchor->getFullURL();
	}

	protected function loadReferencesForPage( Title $from ) {
		/** @var Reference[] $allReferences */
		$allReferences = array();

		foreach( array( 'WikiReference', 'URLReference' ) as $refType ) {
			// find() returns null for error or empty result
			$res = $this->storage->find(
				$refType,
				array(
					'ref_src_wiki' => wfWikiId(),
					'ref_src_namespace' => $from->getNamespace(),
					'ref_src_title' => $from->getDBkey(),
				)
			);

			if ( $res ) {
				/*
				 * We're "cheating", we have no PK!
				 * We used to have a unique index (on all columns), but the size
				 * of the index was too small (urls can be pretty long...)
				 * We have no data integrity reasons to want to ensure unique
				 * entries, and the code actually does a good job of only
				 * inserting uniques. Still, I'll do a sanity check and get rid
				 * of duplicates, should there be any...
				 */
				$res = array_unique( $res );
				$allReferences = array_merge( $allReferences, $res );
			}
		}

		$cache = array();
		foreach( $allReferences as $reference ) {
			if ( !isset( $cache[$reference->getTargetIdentifier()] ) ) {
				$cache[$reference->getTargetIdentifier()] = array();
			}

			$cache[$reference->getTargetIdentifier()][] = $reference;
		}

		$this->referenceCache[$from->getPrefixedDBkey()] = $cache;
	}
}
