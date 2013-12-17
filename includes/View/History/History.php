<?php

namespace Flow\View\History;

use MWTimestamp;
use FakeResultWrapper;

/**
 * History is just an Iterator which takes an array of AbstractRevisions and
 * sorts them on timestamp, DESC.
 * It provides 1 addition method: getTimespan, which will return all records
 * between 2 specified dates.
 */
class History extends FakeResultWrapper {
	/**
	 * @var array
	 */
	protected $records = array();

	/**
	 * @var bool
	 */
	protected $sorted = true;

	/**
	 * @param array $revisions
	 */
	public function __construct( array $revisions = array() ) {
		foreach ( $revisions as $revision ) {
			$record = new HistoryRecord( $revision );

			/*
			 * Instead of saving the real results in $this->result, we're saving
			 * them in $this->records and save the index in $this->result.
			 * That index is timestamp-based, so we can sort them. A unique
			 * part ($index) is added to make sure no records are lost if
			 * multiple had been on the exact same time.
			 *
			 * The reason we're not using $this->result is the getTimespan()
			 * method, where it's easier/more performant to use default array
			 * functions to determine exactly which records fall in a certain
			 * timestamp.
			 */
			$index = count( $this->records );
			$key = $record->getTimestamp()->getTimestamp( TS_MW ) . '-' . $index;
			$this->records[$key] = $record;

			$this->result[] = $key;
		}
		rsort( $this->result );
	}

	/**
	 * Overrides parent class because we're using a second array which holds
	 * our real results (the parent $this->results only holds an index to the
	 * value in the real $this->records class)
	 *
	 * @return HistoryRecord|bool
	 */
	public function fetchRow() {
		if ( $this->numRows() === 0 ) {
			return false;
		}

		$index = parent::fetchRow();
		if ( $index !== false ) {
			$this->currentRow = $this->records[$index];
		}

		return $this->currentRow;
	}

	/**
	 * Returns a subset of History between 2 points in time.
	 *
	 * @param MWTimestamp[optional] $from
	 * @param MWTimestamp[optional] $to
	 * @return History
	 */
	public function getTimespan( MWTimestamp $from = null, MWTimestamp $to = null ) {
		if ( $from === null ) {
			// First Flow commit; no history before this point.
			$from = new MWTimestamp( '20130710000000' );
		}
		if ( $to === null ) {
			// Today; no history after this point.
			$to = new MWTimestamp();
		}

		$from = $from->getTimestamp( TS_MW );
		$to = $to->getTimestamp( TS_MW );

		// Fix bounds in case from & to are switched.
		$min = min( $from, $to );
		$max = max( $from, $to );

		/*
		 * Fastest way to find matching records: add bound timestamps in between
		 * the records' timestamps, re-sort them & find indices of those bounds.
		 */
		$keys = $this->result;
		$keys[] = $min;
		$keys[] = $max;
		rsort( $keys );

		// Because $keys is orders DESC, min is actually the end.
		$end = array_search( $min, $keys ) - 1;
		$start = array_search( $max, $keys );

		$records = array();
		for ( $i = $start; $i < $end && isset( $this->result[$i] ); $i++ ) {
			$records[] = $this->records[$this->result[$i]]->getRevision();
		}

		return new History( $records );
	}
}
