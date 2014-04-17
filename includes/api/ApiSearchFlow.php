<?php

use Flow\Collection\AbstractCollection;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;
use Flow\Search\SearchEngine;
use Flow\Search\Connection;

class ApiSearchFlow extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$searchEngine = new SearchEngine();

		// page is optional; if not provided, all pages will be searched
		$page = null;
		if ( $params['title'] !== null || $params['pageid'] !== null ) {
			$page = $this->getTitleOrPageId( $params );
			$searchEngine->setPage( $page );
		}

		// namespaces is optional; if not provided, all namespaces will be searched
		if ( $params['namespaces'] ) {
			$searchEngine->setNamespaces( $params['namespaces'] );
		}

		$status = $searchEngine->searchText( $params['q'], $params['type'] );

		// searchText returns a Status on failure, or resultset on success
		if ( !$status->isGood() ) {
			$this->dieUsage( $status->getMessage( /* @todo: shortContent, longContext */ ), 'failed-search' );
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
		$revisions = array();
		foreach ( $collections as $collection ) {
			$revisions[] = $collection->getLastRevision();
		}

		$results = $revisions;
		// @todo: figure out how to format the result

		$this->getResult()->addValue( null, $this->getModuleName(), $results );
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

	public function getAllowedParams() {
		return array(
			'q' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
			),
			'title' => null,
			'pageid' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => 'integer',
			),
			'namespaces' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => MWNamespace::getValidNamespaces(),
			),
			'type' => array(
				// false is allowed, means we'll search all types
				ApiBase::PARAM_TYPE => array_merge( Connection::getAllRevisionTypes(), array( false ) ),
				ApiBase::PARAM_DFLT => false,
			)
		);
	}

	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'q' => 'Search term',
			'title' => "Title of the page. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the page. Cannot be used together with {$p}title",
			'namespaces' => 'Namespaces to search in',
			'type' => 'Desired type of results (' . implode( '|', Connection::getAllRevisionTypes() ) . ')',
		);
	}

	public function getDescription() {
		return 'Search within Flow boards';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow-search&q=keyword&title=Main_Page',
		);
	}
}
