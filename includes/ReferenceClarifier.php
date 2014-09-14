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

		// Dont need to do anything with them, they are automatically
		// passed into url generator when loaded.
		$this->storage->getMulti( 'Workflow', $ids );

		// Messages that can be used here:
		// * flow-whatlinkshere-header
		// * flow-whatlinkshere-post
		// Topic and Summary are plain text and do not have links.
		foreach( $references as $reference ) {
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
					'ref_src_namespace' => $from->getNamespace(),
					'ref_src_title' => $from->getDBkey(),
				)
			);
			if ( $res ) {
				$allReferences = array_merge( $allReferences, $res );
			}
		}

		$cache = array();
		foreach( $allReferences as $reference ) {
			if ( ! isset( $cache[$reference->getTargetIdentifier()] ) ) {
				$cache[$reference->getTargetIdentifier()] = array();
			}

			$cache[$reference->getTargetIdentifier()][] = $reference;
		}

		$this->referenceCache[$from->getPrefixedDBkey()] = $cache;
	}
}
