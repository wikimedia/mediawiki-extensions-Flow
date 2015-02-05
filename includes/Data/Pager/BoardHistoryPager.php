<?php

namespace Flow\Data\Pager;

use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Formatter\BoardHistoryQuery;
use Flow\Formatter\FormatterRow;
use Flow\Model\UUID;
use Flow\Model\Workflow;

class BoardHistoryPager extends \ReverseChronologicalPager {
	/**
	 * @var BoardHistoryQuery
	 */
	protected $query;

	/**
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @var UUID|null
	 */
	public $mOffset;

	/**
	 * @var FormatterRow[]
	 */
	public $mResult;

	/**
	 * @param BoardHistoryQuery $query
	 * @param Workflow $workflow
	 */
	public function __construct( BoardHistoryQuery $query, Workflow $workflow ) {
		$this->query = $query;
		$this->workflow = $workflow;

		$this->mDefaultLimit = $this->getUser()->getIntOption( 'rclimit' );
		$this->mIsBackwards = $this->getRequest()->getVal( 'dir' ) == 'prev';
	}

	public function doQuery() {
		$direction = $this->mIsBackwards ? 'rev' : 'fwd';

		// over-fetch so we can figure out if there's anything after what we're showing
		$this->mResult = $this->query->getResults( $this->workflow, $this->getLimit() + 1, $this->mOffset, $direction );
		if ( !$this->mResult ) {
			throw new InvalidDataException(
				'Unable to load topic list history for ' . $this->workflow->getId()->getAlphadecimal(),
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
		$this->mIsFirst = !$this->mOffset || count( $this->query->getResults( $this->workflow, 1, $nextOffset, $reverseDirection ) ) === 0;

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
