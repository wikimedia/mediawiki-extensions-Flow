<?php

namespace Flow\Data\Pager;

use Flow\FlowActions;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Formatter\BoardHistoryQuery;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\TopicHistoryQuery;
use Flow\Formatter\PostHistoryQuery;
use Flow\Model\UUID;

class HistoryPager extends \ReverseChronologicalPager {
	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @var BoardHistoryQuery|TopicHistoryQuery|PostHistoryQuery
	 */
	protected $query;

	/**
	 * @var UUID
	 */
	protected $id;

	/**
	 * @var FormatterRow[]
	 */
	public $mResult;

	// This requests extra to take into account that we will filter some out,
	// to try to reduce the number of rounds (preferably to 1).
	// If you raise this, also increase history_index_limit and bump the
	// key of the indexes using history_index_limit
	const OVERFETCH_FACTOR = 2;

	/**
	 * @param BoardHistoryQuery|TopicHistoryQuery|PostHistoryQuery $query
	 * @param UUID $id
	 */
	public function __construct( FlowActions $actions, /* BoardHistoryQuery|TopicHistoryQuery|PostHistoryQuery */ $query, UUID $id ) {
		$this->actions = $actions;
		$this->query = $query;
		$this->id = $id;

		$this->mDefaultLimit = $this->getUser()->getIntOption( 'rclimit' );
		$this->mIsBackwards = $this->getRequest()->getVal( 'dir' ) == 'prev';
	}

	/**
	 * @param FormatterRow $row
	 * @return bool
	 */
	protected function includeInHistory( FormatterRow $row ) {
		return !$this->actions->getValue( $row->revision->getChangeType(), 'exclude_from_history' );
	}

	/**
	 * Internally query as many times as needed, until there are no more entries
	 * or after filtering, there are the desired number of results
	 *
	 * @param UUID $internalOffset Offset to start from
	 * @param int $limit Number of entries to fetch
	 * @param string $direction Direction, 'fwd' or 'rev'
	 * @return FormatterRow[] Array of history rows
	 */
	protected function doInternalQueries( $internalOffset, $limit, $direction ) {
		$result = array();

		do {
			$remainingNeeded = $limit - count( $result );

			$requestCount = max( 5, self::OVERFETCH_FACTOR * $remainingNeeded );

			// Over-fetch by 1 item so we can figure out when to stop re-querying.
			$resultBeforeFiltering = $this->query->getResults( $this->id, $requestCount + 1, $internalOffset, $direction );

			// We over-fetched, now get rid of redundant value for our "real" data
			$internalOverfetched = null;
			if ( count( $resultBeforeFiltering ) > $requestCount ) {
				// when traversing history reverse, the overfetched entry will be at
				// the beginning of the list; in normal mode it'll be last
				if ( $direction === 'rev' ) {
					$internalOverfetched = array_shift( $resultBeforeFiltering );
				} else {
					$internalOverfetched = array_pop( $resultBeforeFiltering );
				}
			}
			// We needed the exact row counts (before filtering) to determine
			// whether there were was an extra row (which controls pagination).
			// Now we can get rid of rows we don't want to display.  $nextOffset will also
			// be generated based on the last displayed row.
			$resultAfterFiltering = array_values( array_filter( $resultBeforeFiltering, array( $this, 'includeInHistory' ) ) );

			if ( $direction === 'rev' ) {
				$internalOffset = $resultBeforeFiltering[0]->revision->getRevisionId();
				$trimmedResultAfterFiltering = array_slice( $resultAfterFiltering, -$remainingNeeded );
				$result = array_merge( $trimmedResultAfterFiltering, $result );
			} else {
				$internalOffset = $resultBeforeFiltering[count( $resultBeforeFiltering ) - 1]->revision->getRevisionId();
				$trimmedResultAfterFiltering = array_slice( $resultAfterFiltering, 0, $remainingNeeded );
				$result = array_merge( $result, $trimmedResultAfterFiltering );
			}

		} while( count( $result ) < $limit && $internalOverfetched !== null );

		return $result;
	}

	public function doQuery() {
		// $nextOffset is used for mIsFirst which controls a user-visible link
		$nextOffset = UUID::create( $this->mOffset );

		$direction = $this->mIsBackwards ? 'rev' : 'fwd';

		// We over-fetch by 1 *filtered* entry, so if we get it we know the user
		// would actually see that entry if they navigated.
		$this->mResult = $this->doInternalQueries( $nextOffset, $this->getLimit() + 1, $direction );
		if ( !$this->mResult ) {
			throw new InvalidDataException(
				'Unable to load history for ' . $this->id->getAlphadecimal(),
				'fail-load-history'
			);
		}
		$this->mQueryDone = true;

		$overfetched = null;
		if ( count( $this->mResult ) > $this->getLimit() ) {
			// when traversing history reverse, the overfetched entry will be at
			// the beginning of the list; in normal mode it'll be last
			if ( $this->mIsBackwards ) {
				$overfetched = array_shift( $this->mResult );
			} else {
				$overfetched = array_pop( $this->mResult );
			}
		}


		// set some properties that'll be used to generate navigation bar
		$this->mLastShown = $this->mResult[count( $this->mResult ) - 1]->revision->getRevisionId()->getAlphadecimal();
		$this->mFirstShown = $this->mResult[0]->revision->getRevisionId()->getAlphadecimal();

		$nextOffset = $this->mIsBackwards ? $this->mFirstShown : $this->mLastShown;
		$nextOffset = UUID::create( $nextOffset );

		/*
		 * By overfetching, we've already figured out if there's additional
		 * entries at the next page (according to the current direction). Now
		 * go fetch 1 more in the other direction (the one we likely came from,
		 * when navigating)
		 */
		$reverseDirection = $this->mIsBackwards ? 'fwd' : 'rev';
		$this->mIsLast = !$overfetched;
		$this->mIsFirst = !$this->mOffset || count( $this->doInternalQueries( $nextOffset, 1, $reverseDirection ) ) === 0;

		if ( $this->mIsBackwards ) {
			// swap values if we're going backwards
			list( $this->mIsFirst, $this->mIsLast ) = array( $this->mIsLast, $this->mIsFirst );

			// id of the overfetched entry, used to build new links starting at
			// this offset
			if ( $overfetched ) {
				$this->mPastTheEndIndex = $overfetched->revision->getRevisionId()->getAlphadecimal();
			}
		}
	}

	/**
	 * Override pointless parent method.
	 *
	 * @param bool $include
	 * @throws FlowException
	 */
	public function setIncludeOffset( $include ) {
		throw new FlowException( __METHOD__ . ' is not implemented.' );
	}

	// abstract functions required to extend ReverseChronologicalPager

	public function formatRow( $row ) {
		throw new FlowException( __METHOD__ . ' is not implemented.' );
	}

	public function getQueryInfo() {
		return array();
	}

	public function getIndexField() {
		return '';
	}
}
