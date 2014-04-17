<?php

use Flow\Search\SearchEngine;

class ApiSearchFlow extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$searchEngine = new SearchEngine();

		// page is optional; if not provided, all pages will be searched
		$page = null;
		if ( $params['title'] !== null || $params['pageid'] !== null ) {
			$page = $this->getTitleOrPageId( $params );
			$searchEngine->setPage( $page ); // @todo: this method doesn't exist yet ;)
		}

		// namespaces is optional; if not provided, all namespaces will be searched
		if ( $params['namespaces'] ) {
			$searchEngine->setNamespaces( $params['namespaces'] );
		}

		// @todo: make this actually search...
		$results = $searchEngine->searchText( $params['q'] );

		// @todo: format results the way we want

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
		);
	}

	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'q' => 'Search term',
			'title' => "Title of the page. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the page. Cannot be used together with {$p}title",
			'namespaces' => 'Namespaces to search in',
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
