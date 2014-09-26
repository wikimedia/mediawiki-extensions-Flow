<?php

namespace Flow\Data\Pager;

use Flow\Data\Index;
use Flow\Data\ObjectManager;
use Flow\Exception\InvalidInputException;

/**
 * Fetches paginated results from the OM provided in constructor
 */
class Pager {
	private static $VALID_DIRECTIONS = array( 'fwd', 'rev' );
	const DEFAULT_DIRECTION = 'fwd';
	const DEFAULT_LIMIT = 1;
	const MAX_LIMIT = 500;

	/**
	 * @var ObjectManager
	 */
	protected $storage;

	/**
	 * @var Index
	 */
	protected $index;

	/**
	 * @var array Results sorted by the values in this array
	 */
	protected $sort;

	/**
	 * @var array Map of column name to column value for equality query
	 */
	protected $query;

	/**
	 * @var array Options effecting the result such as `sort`, `order`, and `pager-limit`
	 */
	protected $options;

	public function __construct( ObjectManager $storage, array $query, array $options ) {
		// not sure i like this
		$this->storage = $storage;
		$this->query = $query;
		$this->options = $options + array(
			'pager-offset' => null,
			'pager-limit' => self::DEFAULT_LIMIT,
			'pager-dir' => self::DEFAULT_DIRECTION,
		);

		$this->options['pager-limit'] = intval( $this->options['pager-limit'] );
		if ( ! ( $this->options['pager-limit'] > 0 && $this->options['pager-limit'] < self::MAX_LIMIT ) ) {
			$this->options['pager-limit'] = self::DEFAULT_LIMIT;
		}

		if ( !in_array( $this->options['pager-dir'], self::$VALID_DIRECTIONS ) ) {
			$this->options['pager-dir'] = self::DEFAULT_DIRECTION;
		}

		$indexOptions = array(
			'limit' => $this->options['pager-limit']
		);
		if ( isset( $this->options['sort'], $this->options['order'] ) ) {
			$indexOptions += array(
				'sort' => array( $this->options['sort'] ),
				'order' => $this->options['order'],
			);
		}
		$this->sort = $storage->getIndexFor(
			array_keys( $query ),
			$indexOptions
		)->getSort();

		$useId = false;
		foreach ( $this->sort as $val ) {
			if ( substr( $val, -3 ) === '_id' ) {
				$useId = true;
			}
			break;
		}
		$this->offsetKey = $useId ? 'offset-id' : 'offset';
	}

	/**
	 * @return PagerPage
	 */
	public function getPage() {
		$options = $this->options + array(
			// We need one item of leeway to determine if there are more items
			'limit' => $this->options['pager-limit'] + 1,
			'offset-dir' => $this->options['pager-dir'],
			'offset-key' => $this->options['pager-offset'],
			'offset-elastic' => true,
		);

		// Retrieve results
		$results = $this->storage->find( $this->query, $options );

		// null or empty array
		if ( !$results ) {
			return new PagerPage( array(), array(), $this );
		} else {
			return $this->processPage( $results );
		}
	}

	/**
	 * @param array $results
	 * @return PagerPage
	 * @throws InvalidInputException
	 */
	protected function processPage( $results ) {
		$pagingLinks = array();

		// Retrieve paging links
		if ( $this->options['pager-dir'] === 'fwd' ) {
			if ( count( $results ) == $this->options['pager-limit'] + 1 ) {
				// We got one extra, another page exists
				array_pop( $results ); // Discard last item
				$pagingLinks['fwd'] = $this->makePagingLink(
					'fwd',
					end( $results ),
					$this->options['pager-limit']
				);
			}

			if ( $this->options['pager-offset'] !== null ) {
				$pagingLinks['rev'] = $this->makePagingLink(
					'rev',
					reset( $results ),
					$this->options['pager-limit']
				);
			}
		} elseif ( $this->options['pager-dir'] === 'rev' ) {
			if ( count( $results ) == $this->options['pager-limit'] + 1 ) {
				array_shift( $results );

				$pagingLinks['rev'] = $this->makePagingLink(
					'rev',
					reset( $results ),
					$this->options['pager-limit']
				);
			}

			if ( $this->options['pager-offset'] !== null ) {
				$pagingLinks['fwd'] = $this->makePagingLink(
					'fwd',
					end( $results ),
					$this->options['pager-limit']
				);
			}
		} else {
			throw new InvalidInputException( "Unrecognised direction " . $this->options['pager-dir'], 'invalid-input' );
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
		$return = array(
			'offset-dir' => $direction,
			'limit' => $pageLimit,
			$this->offsetKey => $this->storage->serializeOffset( $object, $this->sort ),
		);
		if ( isset( $this->options['sortby'] ) ) {
			$return['sortby'] = $this->options['sortby'];
		}
		return $return;
	}
}
