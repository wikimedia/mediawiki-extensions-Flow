<?php

namespace Flow\Search;

use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Exception\ExceptionInterface;
use PoolCounterWorkViaCallback;
use ProfileSection;
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

		// add aggregation to determine exact amount of matching search terms
		$this->query->addAggregation( $this->termsAggregation( $term ) );

		// @todo: abstract-away this config (core/cirrus also has this - share it somehow?)
		$this->query->setHighlight( array(
			'fields' => array(
				static::HIGHLIGHT_FIELD => array(
					'type' => 'plain',
					'order' => 'score',

					// we want just 1 excerpt of result text, which includes all highlights
					'number_of_fragments' => 1,
					'fragment_size' => 10000, // We want the whole value but more than this is crazy

					// @todo: how to use experimental highlighter instead of plain?
//					'type' => 'experimental',
//					'fragmenter' => 'none',
				),
			),
			'pre_tags' => array( static::HIGHLIGHT_PRE ),
			'post_tags' => array( static::HIGHLIGHT_POST ),
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
	 * We can only do this if dynamic scripting is enabled. In elasticsearch.yml:
	 * script.disable_dynamic: false
	 * @see vendor/ruffin/elastica/test/bin/run_elasticsearch.sh
	 *
	 * @param string $term
	 * @return \Elastica\Aggregation\Sum
	 */
	protected function termsAggregation( $term ) {
		$terms = preg_split( '/\s+/', $term );
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
