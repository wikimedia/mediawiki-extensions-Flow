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

class Searcher {
	/**
	 * @var int
	 */
	const MAX_OFFSET = 100000;

	/**
	 * @var string|false $type
	 */
	protected $type = false;

	/**
	 * @var int[]
	 */
	protected $namespaces = array();

	/**
	 * @var int[]
	 */
	protected $pageIds = array();

	/**
	 * @var string[]|null
	 */
	protected $moderationStates = null;

	/**
	 * @var string
	 */
	protected $indexBaseName;

	/**
	 * @var Query
	 */
	protected $query;

	/**
	 * @var string
	 */
	protected $sort;

	/**
	 * @param int $offset Offset the results by this much
	 * @param int $limit Limit the results to this many
	 * @param string $index Base name for index to search from, defaults to wfWikiId()
	 * @param string|false $type Type of revisions to retrieve
	 */
	public function __construct( $offset, $limit, $index = false, $type = false ) {
		$this->indexBaseName = $index ?: wfWikiId();
		$this->type = $type;

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
	 * Search revisions with provided term.
	 *
	 * @param string $term Term to search
	 * @return Status
	 */
	public function searchText( $term ) {
		$profiler = new ProfileSection( __METHOD__ );

		$filter = new Bool();

		// filters
		if ( $this->namespaces ) {
			$filter->addMust( new Terms( 'namespace', $this->namespaces ) );
		}
		if ( $this->pageIds ) {
			$filter->addMust( new Terms( 'pageid', $this->pageIds ) );
		}
		if ( $this->moderationStates ) {
			$filter->addMust( new Terms( 'revisions.moderation_state', $this->moderationStates ) );
		}

		// only apply filters if there are any
		if ( $filter->toArray() ) {
			$this->query->setFilter( $filter );
		}

		$sortArgs = $this->getSortArgs();
		if ( $sortArgs ) {
			$this->query->setSort( $sortArgs );
		}

		// full-text search
		$queryString = new QueryString( $term );
		$queryString->setFields( array( 'revisions.text' ) );
		$this->query->setQuery( $queryString );

		// @todo: abstract-away this config (core/cirrus also has this - share it somehow?)
		$this->query->setHighlight( array(
			'fields' => array(
				'revisions.text' => array(
					'number_of_fragments' => 1, // Just one of the values in the list
					'fragment_size' => 10000, // We want the whole value but more than this is crazy
					'type' => 'plain', // @todo: we'll probably want fvh (http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-highlighting.html#fast-vector-highlighter)
					'order' => 'score',
				)
			)
		) );

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

	/**
	 * @param int[] $pageIds
	 */
	public function setPageIds( array $pageIds ) {
		$this->pageIds = $pageIds;
	}

	/**
	 * @param int[] $namespaces
	 */
	public function setNamespaces( array $namespaces ) {
		$this->namespaces = $namespaces;
	}

	/**
	 * @param string[]|null $moderationStates
	 */
	public function setModerationStates( array $moderationStates = null ) {
		$this->moderationStates = $moderationStates;
	}

	/**
	 * @param string $sort
	 */
	public function setSort( $sort ) {
		$this->sort = $sort;
	}

	/**
	 * We may want to revisit this at some later point.
	 *
	 * Nik's advice: "I advise against asking Elasticsearch to sort.
	 * Instead layer some kind of boost for more recent posts on top of the
	 * standard text scoring. That way better matches will still get sorted
	 * higher, even if they are older. Also, sorting isn't super efficient
	 * in Elsaticsearch."
	 *
	 * @see https://gerrit.wikimedia.org/r/#/c/126996/6/includes/Search/SearchEngine.php
	 * @return array
	 */
	public function getSortArgs() {
		switch ( $this->sort ) {
			case 'timestamp_asc':
				return array( 'timestamp' => 'asc' );
				break;
			case 'timestamp_desc':
				return array( 'timestamp' => 'desc' );
				break;
			case 'update_timestamp_asc':
				return array( 'update_timestamp' => 'asc' );
				break;
			case 'update_timestamp_desc':
				return array( 'update_timestamp' => 'desc' );
				break;
			case 'relevance':
			default:
				// search results are sorted by relevance by default
				break;
		}
	}
}
