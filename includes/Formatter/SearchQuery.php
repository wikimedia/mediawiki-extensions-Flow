<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Search\Connection;
use Flow\Search\SearchEngine;

class SearchQuery extends AbstractQuery {
	/**
	 * @var SearchEngine
	 */
	protected $searchEngine;

	/**
	 * @param ManagerGroup $storage
	 * @param TreeRepository $treeRepo
	 * @param SearchEngine $searchEngine
	 */
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo, SearchEngine $searchEngine ) {
		parent::__construct( $storage, $treeRepo );
		$this->searchEngine = $searchEngine;
	}

	/**
	 * @param string $q Search string
	 * @param string $type Index type
	 * @param WikiPage[] $pages
	 * @param int[] $namespaces
	 * @return array
	 * @throws \Flow\Exception\InvalidDataException
	 */
	public function getResults( $q, $type, $pages = array(), $namespaces = array() ) {
		// pages are optional; if not provided, all pages will be searched
		if ( $pages ) {
			$this->searchEngine->setPages( $pages );
		}

		// namespaces are optional; if not provided, all namespaces will be searched
		if ( $namespaces ) {
			$this->searchEngine->setNamespaces( $namespaces );
		}

		$status = $this->searchEngine->searchText( $q, $type );
		if ( !$status->isGood() ) {
			throw new InvalidDataException( $status->getMessage(), 'fail-search' );
		}

		// result can be null, if nothing was found
		$result = $status->getValue();
		$results = $result === null ? array() : $result->getResults();

		// loop all results, fetch matched collections
		$collections = array();
		foreach ( $results as $result ) {
			try {
				$id = UUID::create( $result->getId() );
				$collections[] = $this->getCollection( $id, $result->getType() );
			} catch ( FlowException $e ) {
				wfWarn( __METHOD__ . ': ' . $e->getMessage() );
			}
		}

		// load all data for found results
		$results = array();
		foreach ( $collections as $collection ) {
			try {
				$revision = $collection->getLastRevision();
			} catch ( \Exception $e ) {
				wfWarn( __METHOD__ . ': Last revision for ' . $collection->getId()->getAlphadecimal() );
			}

			$results[] = $this->buildResult( $revision, '' /* @todo: $indexField */ );
		}

		return $results;
	}

	/**
	 * Returns the collection object for the given id, based on the collection
	 * type, which can be determined based on the index type.
	 *
	 * @param UUID $id
	 * @param string $type
	 * @return AbstractCollection
	 */
	protected function getCollection( UUID $id, $type ) {
		// @todo: there are more elegant ways to do this

		$map = array(
			Connection::TOPIC_TYPE_NAME => 'Flow\\Collection\\PostCollection',
			Connection::HEADER_TYPE_NAME => 'Flow\\Collection\\HeaderCollection',
		);

		if ( !isset( $map[$type] ) ) {
			throw new InvalidDataException( "Unknown index type: $type", 'fail-load-data' );
		}

		return $map[$type]::newFromId( $id );
	}
}
