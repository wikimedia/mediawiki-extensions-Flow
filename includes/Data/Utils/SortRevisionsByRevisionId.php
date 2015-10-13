<?php

namespace Flow\Data\Utils;

use Flow\InvalidInputException;
use Flow\Model\AbstractRevision;

/**
 * Sorts AbstractRevision objects by revision ID (currently only descending)
 */
class SortRevisionsByRevisionId {
	/**
	 * Order, either ASC or DESC.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * @param string $order ASC or DESC
	 * @throws InvalidInputException
	 */
	public function __construct( $order ) {
		if ( $order !== 'ASC' && $order !== 'DESC' ) {
			throw new InvalidInputException( "Must specify ASC or DESC" );
		}

		$this->order = $order;
	}

	/**
	 * Compares two revisions with descending ordering
	 *
	 * @param AbstractRevision $a
	 * @param AbstractRevision $b
	 */
	public function __invoke( AbstractRevision $a, AbstractRevision $b ) {
		$aId = $a->getRevisionId()->getAlphadecimal();
		$bId = $b->getRevisionId()->getAlphadecimal();

		if ( $aId < $bId ) {
			$result = -1;
		} elseif ( $aId > $bId ) {
			$result = 1;
		} else {
			$result = 0;
		}

		if ( $this->order === 'ASC' ) {
			return $result;
		} else {
			return -$result;
		}
	}
}
