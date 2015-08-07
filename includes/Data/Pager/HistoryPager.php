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
	 * @var UUID|null
	 */
	public $mOffset;

	/**
	 * @var FormatterRow[]
	 */
	public $mResult;

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

	public function doQuery() {
		$direction = $this->mIsBackwards ? 'rev' : 'fwd';

		// over-fetch so we can figure out if there's anything after what we're showing
		$this->mResult = $this->query->getResults( $this->id, $this->getLimit() + 1, $this->mOffset, $direction );
		if ( !$this->mResult ) {
			throw new InvalidDataException(
				'Unable to load history for ' . $this->id->getAlphadecimal(),
				'fail-load-history'
			);
		}
		$this->mQueryDone = true;

		// we over-fetched, now get rid of redundant value for our "real" data
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

		// We needed the exact row counts (before filtering) to determine
		// whether there were was an extra row (which controls pagination).
		// Now we can get rid of rows we don't want to display.  Offsets will also
		// be generated based on the last displayed row.
		$this->mResult = array_values( array_filter( $this->mResult, array( $this, 'includeInHistory' ) ) );

		// set some properties that'll be used to generate navigation bar
		$this->mLastShown = $this->mResult[count( $this->mResult ) - 1]->revision->getRevisionId()->getAlphadecimal();
		$this->mFirstShown = $this->mResult[0]->revision->getRevisionId()->getAlphadecimal();

		/*
		 * By overfetching, we've already figured out if there's additional
		 * entries at the next page (according to the current direction). Now
		 * go fetch 1 more in the other direction (the one we likely came from,
		 * when navigating)
		 */
		$nextOffset = $this->mIsBackwards ? $this->mFirstShown : $this->mLastShown;
		$nextOffset = UUID::create( $nextOffset );
		$reverseDirection = $this->mIsBackwards ? 'fwd' : 'rev';
		$this->mIsLast = !$overfetched;
		$this->mIsFirst = !$this->mOffset || count( $this->query->getResults( $this->id, 1, $nextOffset, $reverseDirection ) ) === 0;

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
