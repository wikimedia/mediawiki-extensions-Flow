<?php

namespace Flow\Data;

class Pager {
	protected $storage, $index;
	protected $defaultLimit = 5;

	public function __construct( ObjectManager $storage, Index $index ) {
		$this->storage = $storage;
		$this->index = $index;
	}

	public function getPage( $query, $options ) {
		$direction = $this->getDirection( $options );
		$offset = $this->getOffset( $options );
		$pageLimit = $this->getLimit( $options );

		// We need one item of leeway to determine if there are more items
		$queryLimit = $pageLimit + 1;

		$options = $options + array(
			'limit' => $queryLimit,
			'offset-dir' => $direction,
			'offset-key' => $offset,
			'offset-elastic' => true,
		);

		// Retrieve results
		$results = $this->storage->find( $query, $options );

		return $this->processPage( $direction, $offset, $pageLimit, &$results );
	}

	public function getDefaultLimit() {
		return $this->defaultLimit;
	}

	public function setDefaultLimit( $newLimit ) {
		$this->defaultLimit = $newLimit;
	}

	protected function getDefaultDirection() {
		return 'fwd';
	}

	protected function validateDirection( $dir ) {
		return in_array( $dir, array( 'fwd', 'rev' ), true );
	}

	protected function processPage( $direction, $offset, $pageLimit, $results ) {
		$pagingLinks = array();

		// Retrieve paging links
		if ( $direction === 'fwd' ) {
			if ( count( $results ) == $pageLimit + 1 ) {
				// We got one extra, another page exists
				array_pop( $results ); // Discard last item
				$pagingLinks['fwd'] = $this->makePagingLink(
					'fwd',
					end( $results ),
					$pageLimit
				);
			}

			if ( $offset !== null ) {
				$pagingLinks['rev'] = $this->makePagingLink(
					'rev',
					reset( $results ),
					$pageLimit
				);
			}
		} elseif ( $direction === 'rev' ) {
			if ( count( $results ) == $pageLimit + 1 ) {
				array_shift( $results );

				$pagingLinks['rev'] = $this->makePagingLink(
					'rev',
					reset( $results ),
					$pageLimit
				);
			}

			if ( $offset !== null ) {
				$pagingLinks['fwd'] = $this->makePagingLink(
					'fwd',
					end( $results ),
					$pageLimit
				);
			}
		} else {
			throw new MWException( "Unrecognised direction $direction" );
		}

		return new PagerPage( $results, $pagingLinks, $this );
	}

	protected function makePagingLink( $direction, $row, $pageLimit ) {
		$offset = $this->storage->serializeOffset( $row, $this->index );
		return array(
			'direction' => $direction,
			'offset' => $offset,
			'limit' => $pageLimit,
		);
	}

	protected function getDirection( $options ) {
		$direction = 'fwd';
		if ( isset( $options['pager-dir'] ) ) {
			if ( $this->validateDirection( $options['pager-dir'] ) ) {
				$direction = $options['pager-dir'];
			}
		}

		return $direction;
	}

	protected function getMaxLimit() {
		return 500;
	}

	protected function getLimit( $options ) {
		if ( isset( $options['pager-limit'] ) ) {
			$requestedLimit = intval( $options['pager-limit'] );

			if ( $requestedLimit > 0 && $requestedLimit < $this->getMaxLimit() ) {
				return $requestedLimit;
			}
		}

		return $this->getDefaultLimit();
	}

	protected function getOffset( $options ) {
		if ( isset( $options['pager-offset'] ) ) {
			return $options['pager-offset'];
		}

		return null;
	}
}

class PagerPage {
	function __construct( $results, $pagingLinks, $pager ) {
		$this->results = $results;
		$this->pagingLinks = $pagingLinks;
		$this->pager = $pager;
	}

	public function getPager() {
		return $this->pager;
	}

	public function getResults() {
		return $this->results;
	}

	public function getPagingLink( $direction ) {
		if ( isset( $this->pagingLinks[$direction] ) ) {
			return $this->pagingLinks[$direction];
		}

		return null;
	}

	public function getPagingLinks() {
		return $this->pagingLinks;
	}
}