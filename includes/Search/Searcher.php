<?php

namespace Flow\Search;

use Elastica\Filter\Bool;
use Elastica\Filter\Terms;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Exception\ExceptionInterface;
use PoolCounterWorkViaCallback;
use ProfileSection;
use Status;
use WikiPage;

class Searcher {
	/**
	 * @var int
	 */
	const MAX_OFFSET = 100000;

	/**
	 * @var int[]
	 */
	protected $namespaces = array();

	/**
	 * @var int[]
	 */
	protected $pageIds = array();

	/**
	 * @var string
	 */
	protected $indexBaseName;

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * @param int $offset Offset the results by this much
	 * @param int $limit Limit the results to this many
	 * @param string $index Base name for index to search from, defaults to wfWikiId()
	 */
	public function __construct( $offset, $limit, $index = false ) {
		$this->indexBaseName = $index ?: wfWikiId();

		$this->query = new Query();

		$offset = min( $offset, self::MAX_OFFSET );
		if ( $offset ) {
			$this->query->setFrom( $offset );
		}

		if ( $limit ) {
			$this->query->setSize( $limit );
		}
	}

	/**
	 * @param WikiPage[] $pages
	 */
	public function setPages( array $pages ) {
		$this->pageIds = array();

		foreach ( $pages as $page ) {
			$this->pageIds[] = $page->getId();
		}
	}

	/**
	 * @param int[] $namespaces
	 */
	public function setNamespaces( array $namespaces ) {
		$this->namespaces = $namespaces;
	}

	/**
	 * Search revisions with provided term.
	 *
	 * @param string $term Term to search
	 * @param string $type Type of revisions to retrieve
	 * @return Status
	 */
	public function searchText( $term, $type ) {
		$profiler = new ProfileSection( __METHOD__ );

		$query = new Query();
		$filter = new Bool();

		// filters
		if ( $this->namespaces ) {
			$filter->addMust( new Terms( 'namespace', $this->namespaces ) );
		}
		if ( $this->pageIds ) {
			$filter->addMust( new Terms( 'pageid', $this->pageIds ) );
		}

		// only apply filters if there are any
		if ( $filter->toArray() ) {
			$query->setFilter( $filter );
		}

		// full-text search
		$queryString = new QueryString( $term );
		$queryString->setFields( array( 'revisions.text' ) );
		$query->setQuery( $queryString );

		// @todo: sort

		$revisionType = Connection::getRevisionType( $this->indexBaseName, $type );
		$search = $revisionType->createSearch( $query );

		// @todo: PoolCounter config at PoolCounterSettings-eqiad.php
		// @todo: do we want this class to extend from ElasticsearchIntermediary and use it's success & failure methods (like CirrusSearch/Searcher does)?

		// Perform the search
		$work = new PoolCounterWorkViaCallback( 'Flow-Search', "_elasticsearch", array(
			'doWork' => function() use ( $search ) {
				try {
					$result = $search->search();
					return Status::newGood( $result );
				} catch ( ExceptionInterface $e ) {
					return Status::newFatal( 'flow-error-search' );
				}
			},
			'error' => function( $status ) {
				$status = $status->getErrorsArray();
				wfLogWarning( 'Pool error searching Elasticsearch:  ' . $status[ 0 ][ 0 ] );
				return Status::newFatal( 'flow-error-search' );
			}
		) );

		wfProfileIn( __METHOD__ . '-execute' );
		$result = $work->execute();
		wfProfileOut( __METHOD__ . '-execute' );

		return $result;
	}
}
