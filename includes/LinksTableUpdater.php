<?php

namespace Flow;

use DeferredUpdates;
use Flow\Data\ManagerGroup;
use Flow\Model\Reference;
use Flow\Model\URLReference;
use Flow\Model\WikiReference;
use Flow\Model\Workflow;
use MediaWiki\MediaWikiServices;
use ParserOutput;
use Title;
use WikiMap;

class LinksTableUpdater {

	/** @var ManagerGroup */
	protected $storage;

	/**
	 * @param ManagerGroup $storage A ManagerGroup
	 */
	public function __construct( ManagerGroup $storage ) {
		$this->storage = $storage;
	}

	public function doUpdate( Workflow $workflow ) {
		$title = $workflow->getArticleTitle();
		$page = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );

		$page->doSecondaryDataUpdates( [ 'defer' => DeferredUpdates::PRESEND ] );
	}

	/**
	 * @param Title $title
	 * @param ParserOutput $parserOutput
	 * @param Reference[]|null $references
	 */
	public function mutateParserOutput( Title $title, ParserOutput $parserOutput, array $references = null ) {
		$references ??= $this->getReferencesForTitle( $title );

		$linkBatch = MediaWikiServices::getInstance()->getLinkBatchFactory()->newLinkBatch();
		/** @var Title[] $internalLinks */
		$internalLinks = [];
		/** @var Title[] $templates */
		$templates = [];

		foreach ( $references as $reference ) {
			if ( $reference->getType() === 'link' ) {
				if ( $reference instanceof URLReference ) {
					$parserOutput->addExternalLink( $reference->getUrl() );
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
					$parserOutput->addImage( $reference->getTitle()->getDBkey() );
				}
			} elseif ( $reference->getType() === 'template' ) {
				if ( $reference instanceof WikiReference ) {
					$templates[$reference->getTitle()->getPrefixedDBkey()] = $reference->getTitle();
					$linkBatch->addObj( $reference->getTitle() );
				}
			}
		}

		$linkBatch->execute();
		$linkCache = MediaWikiServices::getInstance()->getLinkCache();

		foreach ( $internalLinks as $title ) {
			$ns = $title->getNamespace();
			$dbk = $title->getDBkey();
			if ( !isset( $parserOutput->getLinks()[$ns] ) ) {
				$parserOutput->getLinks()[$ns] = [];
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$parserOutput->getLinks()[$ns][$dbk] = $id;
		}

		foreach ( $templates as $title ) {
			$ns = $title->getNamespace();
			$dbk = $title->getDBkey();
			if ( !isset( $parserOutput->getTemplates()[$ns] ) ) {
				$parserOutput->getTemplates()[$ns] = [];
			}

			$id = $linkCache->getGoodLinkID( $title->getPrefixedDBkey() );
			$parserOutput->getTemplates()[$ns][$dbk] = $id;
		}
	}

	public function getReferencesForTitle( Title $title ) {
		$wikiReferences = $this->storage->find(
			'WikiReference',
			[
				'ref_src_wiki' => WikiMap::getCurrentWikiId(),
				'ref_src_namespace' => $title->getNamespace(),
				'ref_src_title' => $title->getDBkey(),
			]
		);

		$urlReferences = $this->storage->find(
			'URLReference',
			[
				'ref_src_wiki' => WikiMap::getCurrentWikiId(),
				'ref_src_namespace' => $title->getNamespace(),
				'ref_src_title' => $title->getDBkey(),
			]
		);

		// let's make sure the merge doesn't fail when nothing was found
		$wikiReferences = $wikiReferences ?: [];
		$urlReferences = $urlReferences ?: [];

		return array_merge( $wikiReferences, $urlReferences );
	}
}
