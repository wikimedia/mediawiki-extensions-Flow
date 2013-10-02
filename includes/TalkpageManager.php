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
	public function __construct( array $occupiedNamespaces, array $occupiedPages ) {
		$this->occupiedNamespaces = $occupiedNamespaces;
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

		return in_array( $title->getPrefixedText(), $this->occupiedPages )
			|| in_array( $title->getNamespace(), $this->occupiedNamespaces );
	}
}
