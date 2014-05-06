<?php

namespace Flow\Data;

use Flow\Exception\InvalidInputException;

class Pager {
	/**
	 * @var ObjectManager
	 */
	protected $storage;

	/**
	 * @var Index
	 */
	protected $index;

	/**
	 * @var integer
	 */
	protected $defaultLimit = 5;

	public function __construct( ObjectManager $storage, array $query, array $options ) {
		// not sure i like this
		$this->storage = $storage;
		$this->sort = $storage->getIndexFor(
			array_keys( $query ),
			array( 'limit' => $options['pager-limit'] )
		)->getSort();
		$this->query = $query;
		$this->options = $options;
	}

	/**
	 * @return PagerPage
	 */
	public function getPage() {
		$direction = $this->getDirection( $this->options );
		$offset = $this->getOffset( $this->options );
		$pageLimit = $this->getLimit( $this->options );

		// We need one item of leeway to determine if there are more items
		$queryLimit = $pageLimit + 1;

		$options = $this->options + array(
			'limit' => $queryLimit,
			'offset-dir' => $direction,
			'offset-key' => $offset,
			'offset-elastic' => true,
		);

		// Retrieve results
		$results = $this->storage->find( $this->query, $options );

		// null or empty array
		if ( !$results ) {
			return new PagerPage( array(), array(), $this );
		} else {
			return $this->processPage( $direction, $offset, $pageLimit, $results );
		}
	}

	/**
	 * @return integer
	 */
	public function getDefaultLimit() {
		return $this->defaultLimit;
	}

	/**
	 * @param integer $newLimit
	 */
	public function setDefaultLimit( $newLimit ) {
		$this->defaultLimit = $newLimit;
	}

	/**
	 * @return string
	 */
	protected function getDefaultDirection() {
		return 'fwd';
	}

	/**
	 * @param string $dir
	 * @return boolean
	 */
	protected function validateDirection( $dir ) {
		return in_array( $dir, array( 'fwd', 'rev' ), true );
	}

	/**
	 * @param string $direction
	 * @param integer $offset
	 * @param integer $pageLimit
	 * @param array $results
	 * @return PagerPage
	 * @throws InvalidInputException
	 */
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
			throw new InvalidInputException( "Unrecognised direction $direction", 'invalid-input' );
		}

		return new PagerPage( $results, $pagingLinks, $this );
	}

	/**
	 * @param string $direction
	 * @param object $object
	 * @param integer $pageLimit
	 * @return array
	 */
	protected function makePagingLink( $direction, $object, $pageLimit ) {
		$offset = $this->storage->serializeOffset( $object, $this->sort );
		return array(
			'direction' => $direction,
			'offset' => $offset,
			'limit' => $pageLimit,
		);
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function getDirection( $options ) {
		$direction = 'fwd';
		if ( isset( $options['pager-dir'] ) ) {
			if ( $this->validateDirection( $options['pager-dir'] ) ) {
				$direction = $options['pager-dir'];
			}
		}

		return $direction;
	}

	/**
	 * @return integer
	 */
	protected function getMaxLimit() {
		return 500;
	}

	/**
	 * @param array $options
	 * @return integer
	 */
	protected function getLimit( $options ) {
		if ( isset( $options['pager-limit'] ) ) {
			$requestedLimit = intval( $options['pager-limit'] );

			if ( $requestedLimit > 0 && $requestedLimit < $this->getMaxLimit() ) {
				return $requestedLimit;
			}
		}

		return $this->getDefaultLimit();
	}

	/**
	 * @param array $options
	 * @return string
	 */
	protected function getOffset( $options ) {
		if ( isset( $options['pager-offset'] ) ) {
			return $options['pager-offset'];
		}

		return null;
	}
}
