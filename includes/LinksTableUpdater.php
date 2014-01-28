<?php

namespace Flow;

use Flow\Data\ManagerGroup;
use Flow\Model\AbstractRevision;
use Flow\Model\Reference;
use Flow\Model\URLReference;
use Flow\Model\UUID;
use Flow\Model\WikiReference;
use LoadBalancer;
use Title;

class LinksTableUpdater {

	protected $storage, $tableConfig, $wikiLb;

	/**
	 * Constructor
	 * @param ManagerGroup $storage A ManagerGroup
	 * @param array $tableConfig Array showing how to turn various references into links table entries.
	 * @param LoadBalancer $wikiLoadBalancer The Database's LoadBalancer.
	 */
	public function __construct( ManagerGroup $storage, array $tableConfig, LoadBalancer $wikiLoadBalancer) {
		$this->storage = $storage;
		$this->tableConfig = $tableConfig;
		$this->wikiLb = $wikiLoadBalancer;
	}

	/**
	 * Executes batch updates for MediaWiki core links tables.
	 * @param  UUID   $objectId         The ID of the object in question.
	 * @param  Title  $title            The Title of the Flow page to add table entries for.
	 * @param  array  $addReferences    References that should be added for that page.
	 * @param  array  $removeReferences References that should be removed for that page.
	 */
	public function updateLinksTables( UUID $objectId, Title $title, array $addReferences, array $removeReferences ) {
		$addByTable = $this->splitByTable( $addReferences );
		$removeByTable = $this->splitByTable( $removeReferences );

		foreach( $addByTable as $tableName => $tableAddReferences ) {
			$this->addLinksToTable( $objectId, $title, $tableName, $tableAddReferences );
		}

		foreach( $removeByTable as $tableName => $tableRemoveReferences ) {
			$this->removeLinksFromTable( $objectId, $title, $tableName, $tableRemoveReferences );
		}
	}

	/**
	 * Inserts a given set of references into the appropriate tables
	 * @param UUID   $objectId The UUID of the object in question
	 * @param Title  $title      The Title of the page to which references should be added
	 * @param string $tableName  The table name of the table to add references to
	 * @param array  $references The references to add
	 */
	protected function addLinksToTable( UUID $objectId, Title $title, $tableName, array $references ) {
		$toInsert = array();

		foreach( $references as $reference ) {
			$insertRow = $this->getWikiRows( $title, $reference );

			$toInsert = array_merge( $insertRow, $toInsert );
		}

		$this->wikiLb->getConnection( DB_MASTER )->replace( $tableName, $this->tableConfig[$tableName]['_indexes'], $toInsert );
	}

	/**
	 * Processes the removal of a set of references from a given object.
	 *
	 * If no references remain on that page, then the item is removed from the
	 * list for that page.
	 * @param UUID   $objectId The UUID of the object in question
	 * @param Title  $title      The Title of the page to which references should be added
	 * @param string $tableName  The table name of the table to remove references from
	 * @param array  $references The references to remove
	 */
	protected function removeLinksFromTable( UUID $objectId, Title $title, $tableName, array $references ) {
		// Search for other references from the same page
		$className = get_class( reset( $references ) );
		$removeReferences = array();

		// Generate DB rows to search for
		$rows = array();
		foreach( $references as $reference ) {
			$row = $reference->getStorageRow();

			// Find all references, not just from this object
			unset( $row['ref_src_object_type'] );
			unset( $row['ref_src_object_id'] );
			// References could come from different workflows
			unset( $row['ref_src_workflow_id'] );

			$rows[] = $row;

			$removeReferences[$reference->getTargetIdentifier()] = $reference;
		}

		$existingReferenceResults = $this->storage->findMulti( $className, $rows );
		$existingReferences = array_reduce( $existingReferenceResults, 'array_merge', array() );

		foreach( $existingReferences as $reference ) {
			if ( ! $reference->getObjectId()->equals( $objectId ) ) {
				// In this case there is an existing reference from another item
				unset( $removeReferences[$reference->getTargetIdentifier()] );
			}
		}

		if ( count( $removeReferences ) ) {
			$this->deleteBatch( $title, $tableName, $removeReferences );
		}
	}

	protected function deleteBatch( Title $title, $tableName, array $references ) {
		$className = get_class( reset( $references ) );

		if ( $className === 'Flow\\Model\\URLReference' ) {
			// Create generalised query.
			// TODO not especially maintainable for new types of UrlReferences
			$conds = $this->getWikiRow( reset( $reference ) );
			$urlFieldName = $this->tableConfig[$tableName]['url'];
			unset( $conds[$urlFieldName] );
			$urls = array();

			foreach( $references as $reference ) {
				$urls[] = $reference->getUrl();
			}

			$conds[$urlFieldName] = $urls;

			$this->wikiLb->getConnection( DB_MASTER )->delete( $tableName, $conds, __METHOD__ );
		} elseif ( $className === 'Flow\\Model\\WikiReference' ) {
			$conds = $this->getWikiRow( $title, reset( $references ) );
			$nsFieldName = $this->tableConfig[$tableName]['namespace'];
			$titleFieldName = $this->tableConfig[$tableName]['title'];

			unset( $conds[$nsFieldName] );
			unset( $conds[$titleFieldName] );

			$titlesByNamespace = array();

			foreach( $references as $reference ) {
				$title = $reference->getTitle();
				if ( ! isset( $titlesByNamespace[$title->getNamespace()] ) ) {
					$titlesByNamespace[$title->getNamespace()] = array();
				}

				$titlesByNamespace[$title->getNamespace()][] = $title->getDBkey();
			}

			foreach( $titlesByNamespace as $ns => $titles ) {
				$this->wikiLb->getConnection( DB_MASTER )->delete(
					$tableName,
					$conds +
						array(
							$nsFieldName => $ns,
							$titleFieldName => $titles,
						),
					__METHOD__
				);
			}
		}
	}

	protected function getWikiRows( Title $title, Reference $reference ) {
		$template = $this->getWikiRow( $title, $reference );

		if ( $reference instanceof URLReference ) {
			$url = $reference->getUrl();
			$parts = wfMakeUrlIndexes( $url );
			$rows = array();

			if ( ! isset( $this->tableConfig[$reference->getWikiTableName()]['url_index'] ) ) {
				return array( $template );
			}

			foreach( $parts as $part ) {
				$rows[] = $template + array(
					$this->tableConfig[$reference->getWikiTableName()]['url_index'] => $part,
				);
			}
		} else {
			return array( $template );
		}
	}

	protected function getWikiRow( Title $title, Reference $reference ) {
		$row = array();
		foreach( $this->tableConfig[$reference->getWikiTableName()] as $item => $field ) {
			switch( $item ) {
				case 'url':
					if ( ! $reference instanceof URLReference ) {
						throw new Flow\Exception\InvalidInputException( "Invalid reference type for url data" );
					}

					$row[$field] = $reference->getUrl();
					break;
				case 'namespace':
					if ( ! $reference instanceof WikiReference ) {
						throw new Flow\Exception\InvalidInputException( "Invalid reference type for namespace data" );
					}

					$row[$field] = $reference->getTitle()->getNamespace();
					break;
				case 'title':
					if ( ! $reference instanceof WikiReference ) {
						throw new Flow\Exception\InvalidInputException( "Invalid reference type for title data" );
					}

					$row[$field] = $reference->getTitle()->getDBkey();
					break;
				case 'src_id':
					$row[$field] = $title->getArticleId();
					break;
				case 'src_namespace':
					$row[$field] = $title->getNamespace();
					break;
				case 'src_title':
					$row[$field] = $title->getDBkey();
					break;
			}
		}

		return $row;
	}

	protected function splitByTable( array $references ) {
		$output = array();

		foreach( $references as $reference ) {
			$tableName = $reference->getWikiTableName();

			if ( ! isset( $output[$tableName] ) ) {
				$output[$tableName] = array();
			}

			$output[$tableName][] = $reference;
		}

		return $output;
	}
}
