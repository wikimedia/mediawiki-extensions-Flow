<?php

namespace Flow\Data\Utils;

use Flow\Model\AbstractRevision;

/**
 * Sorts AbstractRevision objects by revision ID (currently only descending)
 */
class SortRevisionsByRevisionId {
	/**
	 * Compares two revisions with descending ordering
	 *
	 * @param AbstractRevision $a
	 * @param AbstractRevision $b
	 */
	public function __invoke( AbstractRevision $a, AbstractRevision $b ) {
		$aId = $a->getRevisionId()->getAlphadecimal();
		$bId = $b->getRevisionId()->getAlphadecimal();

		// Reverse sort
		if ( $aId < $bId ) {
			return 1;
		} elseif ( $aId > $bId ) {
			return -1;
		} else {
			return 0;
		}
	}
}
