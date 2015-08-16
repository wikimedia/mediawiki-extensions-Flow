<?php

namespace Flow;

use DataUpdate;
use Flow\Data\ManagerGroup;
use Flow\Model\Reference;
use Flow\Model\URLReference;
use Flow\Model\WikiReference;
use Flow\Model\Workflow;
use LinkBatch;
use LinkCache;
use ParserOutput;
use Title;
use WikiPage;

class LinksTableUpdater {

	protected $storage;

	/**
	 * Constructor
	 * @param ManagerGroup $storage A ManagerGroup
	 */
	public function __construct( ManagerGroup $storage ) {
		$this->storage = $storage;
	}

	public function doUpdate( Workflow $workflow ) {
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

	/**
	 * @param Title $title
	 * @param ParserOutput $parserOutput
	 * @param Reference[] $references
	 */
	public function mutateParserOutput( Title $title, ParserOutput $parserOutput, $references = null ) {
		if ( $references === null ) {
			$references = $this->getReferencesForTitle( $title );
		}

		$linkBatch = new LinkBatch();
		/** @var Title[] $internalLinks */
		$internalLinks = array();
		/** @var Title[] $templates */
		$templates = array();

		foreach( $references as $reference ) {
			if ( $reference->getType() === 'link' ) {
				if ( $reference instanceof URLReference ) {
					$parserOutput->mExternalLinks[$reference->getURL()] = true;
				} elseif ( $reference instanceof WikiReference ) {
					$internalLinks[$reference->getTitle()->getPrefixedDBkey()] = $reference->getTitle();
					$linkBatch->addObj( $reference->getTitle() );
				}
			} elseif ( $reference->getType() === WikiReference::TYPE_CATEGORY ) {
				if ( $reference instanceof WikiReference ) {
					$title = $reference->getTitle();
					$parserOutput->addCategory(
						$title->getDBkey(),
						// parsoid moves the sort key into the fragment
						$title->getFragment()
					);
				}
			} elseif ( $reference->getType() === 'file' ) {
				if ( $reference instanceof WikiReference ) {
					// Only local images supported
					$parserOutput->mImages[$reference->getTitle()->getDBkey()] = true;
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
			if ( !isset( $parserOutput->mLinks[$ns] ) ) {
				$parserOutput->mLinks[$ns] = array();
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$parserOutput->mLinks[$ns][$dbk] = $id;
		}

		foreach( $templates as $title ) {
			$ns = $title->getNamespace();
			$dbk = $title->getDBkey();
			if ( !isset( $parserOutput->mTemplates[$ns] ) ) {
				$parserOutput->mTemplates[$ns] = array();
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$parserOutput->mTemplates[$ns][$dbk] = $id;
		}
	}

	public function getReferencesForTitle( Title $title ) {
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
