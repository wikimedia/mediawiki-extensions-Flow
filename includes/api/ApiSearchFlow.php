<?php

use Flow\Container;
use Flow\Formatter\RevisionFormatter;
use Flow\Formatter\SearchQuery;
use Flow\Search\Connection;

class ApiSearchFlow extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		// page is optional; if not provided, all pages will be searched
		$page = null;
		if ( $params['title'] !== null || $params['pageid'] !== null ) {
			$page = $this->getTitleOrPageId( $params );
		}

		/** @var SearchQuery $query */
		$query = Container::get( 'query.search' );
		$rows = $query->getResults( $params['q'], $params['type'], $page, $params['namespaces'] );

		/** @var RevisionFormatter $formatter */
		$formatter = Container::get( 'formatter.revision' );
		$results = array();
		foreach ( $rows as $row ) {
			$results[] = $formatter->formatApi( $row, $this->getContext() );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $results );
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
