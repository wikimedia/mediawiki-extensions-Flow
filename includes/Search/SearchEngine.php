<?php

namespace Flow\Search;

use Flow\Exception\InvalidInputException;

class SearchEngine extends \SearchEngine {
	/**
	 * @var string|false $type Type of revisions to retrieve
	 */
	protected $type = false;

	/**
	 * Unlike \SearchEngine, the default is *no* specific namespace (=ALL)
	 *
	 * @var int[]
	 */
	public $namespaces = array();

	/**
	 * @var int[]
	 */
	protected $pageIds = array();

	/**
	 * @var string[]|null
	 */
	protected $moderationStates = array();

	/**
	 * @var string
	 */
	protected $sort = 'relevance';

	/**
	 * @param string $term text to search
	 * @return Status
	 */
	public function searchText( $term ) {
		$term = trim( $term );
		// No searching for nothing!  That takes forever!
		if ( !$term ) {
			return null;
		}

		$searcher = new Searcher( $this->offset, $this->limit, false, $this->type );
		$searcher->setSort( $this->sort );

		if ( $this->namespaces ) {
			$searcher->setNamespaces( $this->namespaces );
		}
		if ( $this->pageIds ) {
			$searcher->setPageIds( $this->pageIds );
		}
		if ( $this->moderationStates ) {
			$searcher->setModerationStates( $this->moderationStates );
		}

		// @todo: interwiki stuff? (see \CirrusSearch)

		return $searcher->searchText( $term );
	}

	/**
	 * Set the search index to search in.
	 * false is allowed (means we'll search *all* types)
	 *
	 * @param string|false $type
	 * @throws InvalidInputException
	 */
	public function setType( $type ) {
		$allowedTypes = array_merge( Connection::getAllTypes(), array( false ) );
		if ( !in_array( $type, $allowedTypes ) ) {
			throw new InvalidInputException( 'Invalid search sort requested', 'invalid-input' );
		}

		$this->type = $type;
	}

	/**
	 * Set pages in which to search.
	 *
	 * @param int[] $pageIds
	 */
	public function setPageIds( array $pageIds ) {
		$this->pageIds = $pageIds;
	}

	/**
	 * Set moderation states in which to search.
	 *
	 * @param string[] $moderationStates
	 */
	public function setModerationStates( array $moderationStates = null ) {
		$this->moderationStates = $moderationStates;
	}

	/**
	 * @param string $sort
	 * @throws InvalidInputException
	 */
	public function setSort( $sort ) {
		if ( !in_array( $sort, $this->getValidSorts() ) ) {
			throw new InvalidInputException( 'Invalid search sort requested', 'invalid-input' );
		}

		$this->sort = $sort;
	}

	/**
	 * Get the sort of sorts we allow.
	 *
	 * @return array
	 */
	public function getValidSorts() {
		// note that API will default to the first sort in this array - make it
		// a sensible default!
		return array( 'relevance', 'timestamp_asc', 'timestamp_desc', 'update_timestamp_asc', 'update_timestamp_desc' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function supports( $feature ) {
		// we're not really an alternative search engine for MW ;)
		return false;
	}
}
