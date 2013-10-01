<?php

namespace Flow;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	public function isTalkpageOccupied( $title );
}

class TalkpageManager implements OccupationController {

	protected $occupiedPages;

	/**
	 * @param boolean|array $occupiedPages See documentation for $wgFlowOccupyPages
	 */
	public function __construct( $occupiedPages ) {
		$this->occupiedPages = $occupiedPages;
	}

	/**
	 * Determines whether or not a talk page is "occupied" by Flow.
	 *
	 * Internally, determines whether or not 1% of the talk page contains
	 * 99% of the discussions.
	 * @param  Title  $title Title object to check for occupation status
	 * @return boolean True if the talk page is occupied, False otherwise.
	 */
	public function isTalkpageOccupied( $title ) {
		if ( ! $title || ! $title->exists() ) {
			// Page does not exist
			return false;
		}

		$titleText = $title->getPrefixedText();

		if ( $this->occupiedPages === true ) {
			return true;
		} elseif ( is_array( $this->occupiedPages ) ) {
			return in_array( $titleText, $this->occupiedPages );
		} else {
			return false;
		}
	}
}
