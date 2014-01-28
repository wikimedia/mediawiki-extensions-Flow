<?php

namespace Flow;

use Flow\Data\ManagerGroup;
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

	public function getWhatLinksHereProps( $row, $from, $to ) {
		$props = array();
		$references = $this->getWikiReferences( $from, $to );

		// Messages that can be used here:
		// * flow-whatlinkshere-header
		// * flow-whatlinkshere-post
		// Topic and Summary are plain text and do not have links.
		foreach( $references as $reference ) {
			$url = $this->getObjectLink( $reference->getWorkflowId(), $reference->getObjectType(), $reference->getObjectId() );
			$props[] = wfMessage( 'flow-whatlinkshere-' . $reference->getObjectType(), $url )->parse();
		}

		return $props;
	}

	public function getWikiReferences( Title $from, Title $to, $type = null ) {
		if ( ! isset( $this->referenceCache[$from->getPrefixedDBkey()] ) ) {
			$this->loadReferencesForPage( $from );
		}

		$fromT = $from->getPrefixedDBkey();
		$toT = 'title:' . $to->getPrefixedDBkey();

		return isset( $this->referenceCache[$fromT][$toT] ) ?
			$this->referenceCache[$fromT][$toT] :
			array();
	}

	protected function getObjectLink( UUID $workflow, $objectType, UUID $objectId ) {
		if ( $objectType === 'post' ) {
			$fragment = 'flow-post-' . $objectId->getAlphadecimal();
		} elseif ( $objectType === 'header' ) {
			$fragment = 'flow-header-content';
		}

		return $this->urlGenerator->generateUrl( $workflow, 'view', array(), $fragment );
	}

	protected function loadReferencesForPage( Title $from ) {
		$allReferences = array();

		foreach( array( 'WikiReference', 'URLReference' ) as $refType ) {
			$allReferences = array_merge( $allReferences, $this->storage->find(
				$refType,
				array(
					'ref_src_namespace' => $from->getNamespace(),
					'ref_src_title' => $from->getDBkey(),
				)
			) );
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