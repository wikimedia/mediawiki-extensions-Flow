<?php

namespace Flow\Search;

use WikiPage;

class SearchEngine extends \SearchEngine { // @todo: \CirrusSearch \SearchEngine? or nothing? we'll see what's useful to inherit...
	/**
	 * @var WikiPage|null
	 */
	protected $page;

	/**
	 * {@inheritDoc}
	 */
	public function supports( $feature ) {
		// @todo: have yet to figure this out, but I assume we won't support any of these normal features, whatever they may be ;)
		return false;
	}

	/**
	 * Overridden to delegate prefix searching to Searcher.
	 *
	 * @param string $term text to search
	 * @param string|false $type Type of revisions to retrieve
	 * @return ResultSet|null|Status results, no results, or error respectively
	 */
	public function searchText( $term, $type = false ) {
		$term = trim( $term );
		// No searching for nothing!  That takes forever!
		if ( !$term ) {
			return null;
		}

		$searcher = new Searcher( $this->offset, $this->limit, $this->page, $this->namespaces );

		// Ignore leading ~ because it is used to force displaying search results but not to effect them
		if ( substr( $term, 0, 1 ) === '~' )  {
			$term = substr( $term, 1 );
			$searcher->addSuggestPrefix( '~' );
		}

/*
@todo: figure out how this namespace-based search works
@todo: then also implement page-based search
		if ( $this->lastNamespacePrefix ) {
			$searcher->addSuggestPrefix( $this->lastNamespacePrefix );
		}
*/

		// TODO remove this when we no longer have to support core versions without
		// Ie946150c6796139201221dfa6f7750c210e97166
		if ( method_exists( $this, 'getSort' ) ) {
			$searcher->setSort( $this->getSort() );
		}

		// @todo: self::MORE_LIKE_THIS_PREFIX stuff? (see parent)
		// @todo: interwiki stuff? (see parent)
		$status = $searcher->searchText( $term, $type );

		// For historical reasons all callers of searchText interpret any Status return as an error
		// so we must unwrap all OK statuses.  Note that $status can be "good" and still contain null
		// since that is interpreted as no results.
		return $status->isOk() ? $status->getValue() : $status;
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
		// @todo: how is data sorted?
		// @todo: we may want to sort by mo recent too?
		return array( 'relevance', 'title_asc', 'title_desc' );
	}
}
