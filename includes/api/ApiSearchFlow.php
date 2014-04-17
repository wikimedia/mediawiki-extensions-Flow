<?php

use Flow\Container;
use Flow\Formatter\RevisionFormatter;
use Flow\Formatter\SearchQuery;
use Flow\Search\Connection;

class ApiSearchFlow extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$pages = $this->getTitlesOrPageIds( $params );

		/** @var SearchQuery $query */
		$query = Container::get( 'query.search' );
		$rows = $query->getResults( $params['q'], $params['type'], $pages, $params['namespaces'] );

		/** @var RevisionFormatter $formatter */
		$formatter = Container::get( 'formatter.revision' );
		$results = array();
		foreach ( $rows as $row ) {
			$results[] = $formatter->formatApi( $row, $this->getContext() );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $results );
	}

	/**
	 * Parent has a method (getTitleOrPageId) to retrieve a WikiPage object
	 * based on 'title' or 'pageid' parameter. However, we'd prefer to accept
	 * multiple pages.
	 * This will provide similar functionality, but for arrays of 'titles' or
	 * 'pageids', instead of just one value (returning an array of WikiPage
	 * objects instead of just 1)
	 *
	 * @param array $params
	 * @return WikiPage[]
	 */
	protected function getTitlesOrPageIds( $params ) {
		// pages are optional; if not provided, all pages will be searched;
		// however, only 1 of these 2 parameters (titles or pageids) is allowed
		if ( $params['titles'] || $params['pageids'] ) {
			$this->requireOnlyOneParameter( $params, 'titles', 'pageids' );
		}

		$values = array();
		if ( $params['titles'] ) {
			$type = 'title';
			$values = $params['titles'];
		} elseif ( $params['pageids'] ) {
			$type = 'pageid';
			$values = $params['pageids'];
		}

		// feed parent's getTitleOrPageId method a stub $params array to
		// retrieve the requested page objects
		$pages = array();
		foreach ( $values as $value ) {
			$dummyParams = array( $type => $value );
			$pages[] = $this->getTitleOrPageId( $dummyParams );
		}

		return $pages;
	}

	public function getAllowedParams() {
		return array(
			'q' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
			),
			'titles' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => 'string',
			),
			'pageids' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => 'integer',
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
			'titles' => "Title of the page. Cannot be used together with {$p}pageids",
			'pageids' => "ID of the page. Cannot be used together with {$p}titles",
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
