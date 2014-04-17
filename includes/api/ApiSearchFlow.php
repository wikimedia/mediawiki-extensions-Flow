<?php

use Flow\Container;
use Flow\Formatter\RevisionFormatter;
use Flow\Formatter\SearchQuery;
use Flow\Search\Connection;

class ApiSearchFlow extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$pageIds = array();
		// page is optional - if not provided, all pages will be searched
		if ( $params['title'] || $params['pageid'] ) {
			$page = $this->getTitleOrPageId( $params );

			// validate the page that was passed in
			if ( !$page->exists() ) {
				$this->dieUsage( 'Invalid page provided', 'invalid-page' );
			}

			/** @var Flow\TalkpageManager $controller */
			$controller = Container::get( 'occupation_controller' );
			if ( !$controller->isTalkpageOccupied( $page->getTitle() ) ) {
				$this->dieUsage( 'Page provided does not have Flow enabled', 'invalid-page' );
			}

			$pageIds[] = $page->getId();
		}

		/** @var SearchQuery $query */
		$query = Container::get( 'query.search' );
		$rows = $query->getResults( $params['q'], $params['type'], $pageIds, $params['namespaces'] );

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
				ApiBase::PARAM_TYPE => 'integer'
			),
			'namespaces' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => MWNamespace::getValidNamespaces(),
			),
			'type' => array(
				// false is allowed (means we'll search *all* types)
				ApiBase::PARAM_TYPE => array_merge( Connection::getAllRevisionTypes(), array( false ) ),
				ApiBase::PARAM_DFLT => false,
			)
		);
	}

	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'q' => 'Search term',
			'title' => "Title of the boards to search in. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the boards to search in. Cannot be used together with {$p}title",
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
