<?php

namespace Flow\Search;

use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Exception\ExceptionInterface;
use PoolCounterWorkViaCallback;
use ProfileSection;
use Status;

class Searcher {
	/**
	 * @var string|false $type
	 */
	protected $type = false;

	/**
	 * @var string
	 */
	protected $indexBaseName;

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * @param Query $query
	 * @param string|bool $index Base name for index to search from, defaults to wfWikiId()
	 * @param string|bool $type Type of revisions to retrieve, defaults to all
	 */
	public function __construct( Query $query, $index = false, $type = false ) {
		$this->query = $query;
		$this->indexBaseName = $index ?: wfWikiId();
		$this->type = $type;
	}

	/**
	 * Search revisions with provided term.
	 *
	 * @param string $term Term to search
	 * @return Status
	 */
	public function searchText( $term ) {
		$profiler = new ProfileSection( __METHOD__ );

		// full-text search
		$queryString = new QueryString( $term );
		$queryString->setFields( array( 'revisions.text' ) );
		$this->query->setQuery( $queryString );

		// @todo: support insource: queries (and perhaps others)

		$revisionType = Connection::getRevisionType( $this->indexBaseName, $this->type );
		$search = $revisionType->createSearch( $this->query );

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
