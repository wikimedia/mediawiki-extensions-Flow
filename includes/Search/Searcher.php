<?php

namespace Flow\Search;

use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Exception\ExceptionInterface;
use Elastica\Request;
use Flow\Container;
use PoolCounterWorkViaCallback;
use Status;

class Searcher {
	const HIGHLIGHT_FIELD = 'revisions.text';
	const HIGHLIGHT_PRE = '<span class="searchmatch">';
	const HIGHLIGHT_POST = '</span>';

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
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @param Query $query
	 * @param string|bool $index Base name for index to search from, defaults to wfWikiId()
	 * @param string|bool $type Type of revisions to retrieve, defaults to all
	 */
	public function __construct( Query $query, $index = false, $type = false ) {
		$this->query = $query;
		$this->indexBaseName = $index ?: wfWikiId();
		$this->type = $type;
		$this->connection = Container::get( 'search.connection' );
	}

	/**
	 * Search revisions with provided term.
	 *
	 * @param string $term Term to search
	 * @return Status
	 */
	public function searchText( $term ) {
		// full-text search
		$queryString = new QueryString( $term );
		$queryString->setFields( array( 'revisions.text' ) );
		$this->query->setQuery( $queryString );

		// add aggregation to determine exact amount of matching search terms
		$terms = $this->getTerms( $term );
		$this->query->addAggregation( $this->termsAggregation( $terms ) );

		// @todo: abstract-away this config? (core/cirrus also has this - share it somehow?)
		$this->query->setHighlight( array(
			'fields' => array(
				static::HIGHLIGHT_FIELD => array(
					'type' => 'plain',
					'order' => 'score',

					// we want just 1 excerpt of result text, which includes all highlights
					'number_of_fragments' => 1,
					'fragment_size' => 10000, // We want the whole value but more than this is crazy
				),
			),
			'pre_tags' => array( static::HIGHLIGHT_PRE ),
			'post_tags' => array( static::HIGHLIGHT_POST ),
		) );

		// @todo: support insource: queries (and perhaps others)

		$searchable = $this->connection->getFlowIndex( $this->indexBaseName );
		if ( $this->type !== false ) {
			$searchable = $searchable->getType( $this->type );
		}
		$search = $searchable->createSearch( $this->query );

		// @todo: PoolCounter config at PoolCounterSettings-eqiad.php
		// @todo: do we want this class to extend from ElasticsearchIntermediary and use its success & failure methods (like CirrusSearch/Searcher does)?

		// Perform the search
		$work = new PoolCounterWorkViaCallback( 'Flow-Search', "_elasticsearch", array(
			'doWork' => function() use ( $search ) {
				try {
					$result = $search->search();
					return Status::newGood( $result );
				} catch ( ExceptionInterface $e ) {
					if ( strpos( $e->getMessage(), 'dynamic scripting for [groovy] disabled' ) ) {
						// known issue with default ES config, let's display a more helpful message
						return Status::newFatal( new \RawMessage(
							"Couldn't complete search: dynamic scripting needs to be enabled. " .
							"Please add 'script.disable_dynamic: false' to your elasticsearch.yml"
						) );
					}

					return Status::newFatal( 'flow-error-search' );
				}
			},
			'error' => function( Status $status ) {
				$status = $status->getErrorsArray();
				wfLogWarning( 'Pool error searching Elasticsearch: ' . $status[0][0] );
				return Status::newFatal( 'flow-error-search' );
			}
		) );

		$result = $work->execute();

		return $result;
	}

	/**
	 * We want to retrieve the total amount of search word hits
	 * (static::termsAggregation) but our search terms may not be how
	 * ElasticSearch stores the words in its index.
	 * Elastic will "analyze" text (perform stemming, etc) and store
	 * the terms in a normalized way.
	 * AFAICT, there is not really a way to get to that information
	 * from within a search query.
	 *
	 * Luckily, since 1.0, Elastic supports _termvector, which gives
	 * you statistics about the terms in your document.
	 * Since 1.4, Elastic supports feeding _termvector documents to
	 * analyze.
	 * We're going to (ab)use this by letting it respond with term
	 * information on a bogus document that contains only our current
	 * search terms.
	 * So we'll give it a document with just our keywords for the
	 * column that we're searching in (revisions.text) and Elastic will
	 * use that column's configuration to analyze the text we feed it.
	 * It will then respond with the normalized terms & their stats.
	 *
	 * @param string $terms
	 * @return array
	 */
	protected function getTerms( $terms ) {
		$terms = preg_split( '/\s+/', $terms );

		// _termvectors only works on a type, but our types are
		// configured exactly the same so it doesn't matter which
		$types = Connection::getAllTypes();
		$searchable = $this->connection->getFlowIndex( $this->indexBaseName );
		$searchable = $searchable->getType( array_pop( $types ) );

		$query = array(
			// bogus document that contains the current search term
			'doc' => array(
				'revisions' => array(
					'text' => $terms,
				),
			),
			"fields" => array( "revisions.text" ),
		);

		// Elastica has no abstraction over _termvector like it has
		// for _query, so just do the request ourselves
		$response = $searchable->request(
			'_termvector',
			Request::POST,
			$query,
			array()
		);

		$data = $response->getData();
		return array_keys( $data['term_vectors']['revisions.text']['terms'] );
	}

	/**
	 * We can only do this if dynamic scripting is enabled. In elasticsearch.yml:
	 * script.disable_dynamic: false
	 * @see vendor/ruffin/elastica/test/bin/run_elasticsearch.sh
	 *
	 * @param array $terms
	 * @return \Elastica\Aggregation\Sum
	 */
	protected function termsAggregation( array $terms ) {
		$terms = str_replace( '"', '\\"', $terms );

		$script = '
keywords = ["' . implode( '","', $terms ) . '"]
total = 0
for (term in keywords) {
	total += _index["revisions.text"][term].tf()
}
return total';
		$script = new \Elastica\Script( $script, null, 'groovy' );

		$aggregation = new \Elastica\Aggregation\Sum( 'ttf' );
		// $aggregation->setScript() doesn't seem to properly set 'lang': 'groovy'
		// see https://github.com/ruflin/Elastica/pull/748
		// $aggregation->setScript( $script );
		$aggregation->setParams( array( 'lang' => 'groovy' ) );
		$aggregation->setParam( 'script', $script->getScript() );

		return $aggregation;
	}
}
