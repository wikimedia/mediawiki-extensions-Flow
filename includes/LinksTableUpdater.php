<?php

namespace Flow;

use DataUpdate;
use Flow\Data\ManagerGroup;
use Flow\Model\URLReference;
use Flow\Model\WikiReference;
use LinkBatch;
use LinkCache;
use LoadBalancer;
use WikiPage;

class LinksTableUpdater {

	protected $storage;

	/**
	 * Constructor
	 * @param ManagerGroup $storage A ManagerGroup
	 * @param array $tableConfig Array showing how to turn various references into links table entries.
	 * @param LoadBalancer $wikiLoadBalancer The Database's LoadBalancer.
	 */
	public function __construct( ManagerGroup $storage ) {
		$this->storage = $storage;
	}

	public function doUpdate( $workflow ) {
		$title = $workflow->getArticleTitle();
		$page = WikiPage::factory( $title );
		$content = $page->getContent();
		if ( $content === null ) {
			$updates = array();
		} else {
			$updates = $content->getSecondaryDataUpdates( $title );
		}

		DataUpdate::runUpdates( $updates );
	}

	public function mutateLinksUpdate( $linksUpdate, $references = false ) {
		$title = $linksUpdate->getTitle();

		if ( $references === false ) {
			$references = $this->getReferencesForTitle( $title );
		}

		$linkBatch = new LinkBatch();
		$internalLinks = array();
		$templates = array();

		foreach( $references as $reference ) {
			if ( $reference->getType() === 'link' ) {
				if ( $reference instanceof URLReference ) {
					$linksUpdate->mExternals[$reference->getURL()] = true;
				} elseif ( $reference instanceof WikiReference ) {
					$internalLinks[$reference->getTitle()->getPrefixedDBkey()] = $reference->getTitle();
					$linkBatch->addObj( $reference->getTitle() );
				}
			} elseif ( $reference->getType() === 'file' ) {
				if ( $reference instanceof WikiReference ) {
					// Only local images supported
					$linksUpdate->mImages[$reference->getTitle()->getDBkey()] = true;
				}
			} elseif ( $reference->getType() === 'template' ) {
				if ( $reference instanceof WikiReference ) {
					$templates[$reference->getTitle()->getPrefixedDBkey()] = $reference->getTitle();
					$linkBatch->addObj( $reference->getTitle() );
				}
			}
		}

		$linkBatch->execute();
		$linkCache = LinkCache::singleton();

		foreach( $internalLinks as $title ) {
			$ns = $title->getNamespace();
			$dbk = $title->getDBkey();
			if ( !isset( $linksUpdate->mLinks[$ns] ) ) {
				$linksUpdate->mLinks[$ns] = array();
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$linksUpdate->mLinks[$ns][$dbk] = $id;
		}

		foreach( $templates as $title ) {
			$ns = $title->getNamespace();
			$dbk = $title->getDBkey();
			if ( !isset( $linksUpdate->mTemplates[$ns] ) ) {
				$linksUpdate->mTemplates[$ns] = array();
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$linksUpdate->mTemplates[$ns][$dbk] = $id;
		}
	}

	public function getReferencesForTitle( $title ) {
		global $wgFlowMigrateReferenceWiki;
		$wikiConds = $wgFlowMigrateReferenceWiki
			? array()
			: array( 'ref_src_wiki' => wfWikiId() );

		$wikiReferences = $this->storage->find(
			'WikiReference',
			$wikiConds + array(
				'ref_src_namespace' => $title->getNamespace(),
				'ref_src_title' => $title->getDBkey(),
			)
		);

		$urlReferences = $this->storage->find(
			'URLReference',
			$wikiConds + array(
				'ref_src_namespace' => $title->getNamespace(),
				'ref_src_title' => $title->getDBkey(),
			)
		);

		// let's make sure the merge doesn't fail when nothing was found
		$wikiReferences = $wikiReferences ?: array();
		$urlReferences = $urlReferences ?: array();

		return array_merge( $wikiReferences, $urlReferences );
	}
}
