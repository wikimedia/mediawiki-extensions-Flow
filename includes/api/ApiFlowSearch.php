<?php

use Flow\Container;
use Flow\Exception\InvalidDataException;
use Flow\Formatter\RevisionFormatter;
use Flow\Formatter\SearchQuery;
use Flow\Search\Connection;
use Flow\Search\SearchEngine;

class ApiFlowSearch extends ApiFlowBaseGet {
	/**
	 * @var SearchQuery
	 */
	protected $query;

	/**
	 * @var SearchEngine
	 */
	protected $searchEngine;

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'q' );
		$this->query = Container::get( 'query.search' );
		$this->searchEngine = new SearchEngine();
	}

	// @todo: Flow API needs &page= -> useless here, shouldn't be required (or shouldn't this be a submodule of ApiFlow?)

	public function execute() {
		$params = $this->extractRequestParams();

		$this->searchEngine->setType( $params['type'] );
		$this->searchEngine->setLimitOffset( $params['limit'], $params['offset'] );
		$this->searchEngine->setSort( $params['sort'] );

		// pages are optional; if not provided, all pages will be searched
		$pageIds = $this->getPageIds( $params );
		if ( $pageIds ) {
			$this->searchEngine->setPageIds( $pageIds );
		}

		// namespaces are optional; if not provided, all namespaces will be searched
		if ( $params['namespaces'] ) {
			$this->searchEngine->setNamespaces( $params['namespaces'] );
		}

		// moderationStates are optional; if not provided, all moderation states will be searched
		$moderationState = $this->getModerationState( $params );
		if ( $moderationState ) {
			$this->searchEngine->setModerationStates( $moderationState );
		}

		/** @var Status $status */
		$status = $this->searchEngine->searchText( $params['term'] );
		if ( !$status->isGood() ) {
			throw new InvalidDataException( $status->getMessage(), 'fail-search' );
		}

		/** @var \Elastica\ResultSet|null $result */
		$result = $status->getValue();
		// result can be null, if nothing was found
		$results = $result === null ? array() : $result->getResults();

/*
		// @todo: this shows highlights; needs better integration, just test code for now ;)
		foreach ( $results as $result ) {
			var_dump($result->getHighlights());
		}
		exit;
*/

		$rows = $this->query->getResults( $results );
		$results = array(
			'total' => $result->getTotalHits(),
			'rows' => array(),
		);

		/** @var RevisionFormatter $formatter */
		$formatter = Container::get( 'formatter.revision' );
		foreach ( $rows as $row ) {
			$results['rows'][] = $formatter->formatApi( $row, $this->getContext() );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $results );
	}

	/**
	 * @param array $params
	 * @return int[]
	 */
	protected function getPageIds( $params ) {
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

		return $pageIds;
	}

	/**
	 * If only state AbstractRevision::MODERATED_NONE (which is '') is
	 * passed in, this param will be an empty array. Let's fix this and
	 * turn it into an array with exactly 1 value: ''
	 * Other scenarios are fine: if multiple states are passed in, ''
	 * is correctly recognized. If no state has been passed in, this
	 * parameter value is null, which will by default search all
	 * moderation states.
	 * Regardless of which status a user searches in, we'll never
	 * display results the user has no permissions for.
	 *
	 * @param array $params
	 * @return string[]|null
	 */
	protected function getModerationState( $params ) {
		$moderationState = $params['moderationState'];
		if ( $moderationState !== null && count( $moderationState ) === 0 ) {
			$moderationState = array( '' );
		}

		return $moderationState;
	}

	public function getAllowedParams() {
		global $wgFlowDefaultLimit;

		$sorts = $this->searchEngine->getValidSorts();

		return array(
			'term' => array(
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
			'moderationState' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => $this->getModerationStates( false ),
			),
			'sort' => array(
				ApiBase::PARAM_TYPE => $sorts,
				ApiBase::PARAM_DFLT => reset( $sorts ),
			),
			'type' => array(
				// false is allowed (means we'll search *all* types)
				ApiBase::PARAM_TYPE => array_merge( Connection::getAllTypes(), array( false ) ),
				ApiBase::PARAM_DFLT => false,
			),
			'offset' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => $wgFlowDefaultLimit,
				ApiBase::PARAM_MAX => $wgFlowDefaultLimit,
				ApiBase::PARAM_MAX2 => $wgFlowDefaultLimit,
			),
		);
	}

	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'term' => 'Search term',
			'title' => "Title of the boards to search in. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the boards to search in. Cannot be used together with {$p}title",
			'namespaces' => 'Namespaces to search in',
			'moderationState' => 'Search for revisions in (a) particular moderation state(s)',
			'sort' => 'What to order the search results by',
			'type' => 'Desired type of results (' . implode( '|', Connection::getAllTypes() ) . ')',
			'offset' => 'Offset value to start fetching results at',
			'limit' => 'Amount of results to fetch',
		);
	}

	public function getDescription() {
		return 'Search within Flow boards';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=search&qterm=keyword&qtitle=Main_Page',
		);
	}

	protected function getBlockParams() {
		// irrelevant for search API
		return array();
	}

	protected function getAction() {
		// irrelevant for search API
		return '';
	}
}
