<?php

namespace Flow\Search;

use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Exception\ExceptionInterface;
use ProfileSection;
use Status;
use WikiPage;

class Searcher {
	/**
	 * @var int
	 */
	const MAX_OFFSET = 100000;

	/**
	 * @var int[]|null
	 */
	protected $namespaces;

	/**
	 * @var WikiPage|null
	 */
	protected $page;

	/**
	 * @var int
	 */
	protected $offset;

	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @var string
	 */
	protected $indexBaseName;

	/**
	 * @param int $offset Offset the results by this much
	 * @param int $limit Limit the results to this many
	 * @param WikiPage|null Page to search in
	 * @param int[]|null Namespaces to search in
	 * @param string $index Base name for index to search from, defaults to wfWikiId()
	 */
	public function __construct( $offset, $limit, WikiPage $page = null, array $namespaces = null, $index = false ) {
		$this->offset = min( $offset, self::MAX_OFFSET );
		$this->limit = $limit;
		$this->page = $page;
		$this->namespaces = $namespaces;
		$this->indexBaseName = $index ?: wfWikiId();
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

		if( $this->offset ) {
			$query->setFrom( $this->offset );
		}
		if( $this->limit ) {
			$query->setSize( $this->limit );
		}

		// full-text search
		$query->setQuery( new QueryString( $term ) );

		// @todo: sort
		// @todo: namespace
		// @todo: page

		$revisionType = Connection::getRevisionType( $this->indexBaseName, $type );
		$search = $revisionType->createSearch( $query );

		wfProfileIn( __METHOD__ . '-request' );
		try {
			// @todo: Probably want to stuff it in the a pool counter.
			// see CirrusSearch/Searcher
			$result = $search->search();
			$status = Status::newGood( $result );
		} catch ( ExceptionInterface $e ) {
			$status = Status::newFatal( 'cirrussearch-backend-error' ); // @todo: Flow-specific error msg
		}
		wfProfileOut( __METHOD__ . '-request' );

		return $status;
	}
}
