<?php

namespace Flow\Search;

use WikiPage;

class SearchEngine extends \SearchEngine { // @todo: \CirrusSearch? \SearchEngine? or nothing? we'll see what's useful to inherit...
	/**
	 * @var WikiPage|null
	 */
	protected $page;

	/**
	 * {@inheritDoc}
	 */
	public function supports( $feature ) {
		// we're not really an alternative search engine for MW ;)
		return false;
	}

	/**
	 * @param string $term text to search
	 * @param string|false $type Type of revisions to retrieve
	 * @return Status
	 */
	public function searchText( $term, $type = false ) {
		$term = trim( $term );
		// No searching for nothing!  That takes forever!
		if ( !$term ) {
			return null;
		}

		$searcher = new Searcher( $this->offset, $this->limit, $this->page, $this->namespaces );

		// TODO remove this when we no longer have to support core versions without
		// Ie946150c6796139201221dfa6f7750c210e97166
		if ( method_exists( $this, 'getSort' ) ) {
			$searcher->setSort( $this->getSort() );
		}

		// @todo: interwiki stuff? (see parent)
		// @todo: figure out how this namespace-based search works
		// @todo: then also implement page-based search
		return $searcher->searchText( $term, $type );
	}

	/**
	 * Search a specific page only.
	 *
	 * @param WikiPage $page
	 */
	public function setPage( WikiPage $page ) {
		$this->page = $page;
	}

	/**
	 * Get the sort of sorts we allow.
	 *
	 * @return array
	 */
	public function getValidSorts() {
		/*
		 * @todo: Nik's advice: "I advise against asking Elasticsearch to sort.
		 * Instead layer some kind of boost for more recent posts on top of the
		 * standard text scoring. That way better matches will still get sorted
		 * higher, even if they are older. Also, sorting isn't super efficient
		 * in Elsaticsearch.
		 * @see: https://gerrit.wikimedia.org/r/#/c/126996/6/includes/Search/SearchEngine.php
		 */

		// @todo: how is data sorted?
		// @todo: we may want to sort by mo recent too?
		return array( 'relevance', 'title_asc', 'title_desc' ); // @todo: stolen from Cirrus; adjust to what we need
	}
}
